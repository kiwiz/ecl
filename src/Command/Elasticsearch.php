<?php

namespace ECL\Command;

/**
 * Class Elasticsearch
 * Executes an ES query.
 * @package ECL
 */
class Elasticsearch extends \ECL\Command {
    /** @var \Elasticsearch\Elasticsearch Client object. */
    private $client = null;
    /** @var \Elasticsearch\Elasticsearch Client object for indexing. */
    private $index_client = null;
    /** @var array Query structure. */
    private $query = [];
    /** @var array List of settings to apply. */
    private $settings = [];
    /** @var \ECL\Command\Elasticsearch\Agg|null $agg Optional aggregation. */
    private $agg = null;
    /** @var callable|null Connection provider. */
    private $conn_provider = null;

    const LUT_INDEX = 'ecl_lookup';
    const LUT_TYPE = 'table';
    const LUT_PATH = 'entries';
    /** How long a scroll cursor is valid. */
    const CUR_TTL = '10s';

    /**
     * @param array $query Query structure.
     * @param \ECL\Command\Elasticsearch\Agg|null $agg Optional aggregation.
     * @param array $settings List of settings to apply.
     */
    public function __construct($query=[], $agg=null, array $settings=[]) {
        $this->query = $query;
        $this->agg = $agg;
        $this->settings = $settings;

        $hosts = \ECL\Util::get($settings, 'hosts', []);
        $index_hosts = \ECL\Util::get($settings, 'index_hosts', []);
        if(count($index_hosts) == 0) {
            $index_hosts = $hosts;
        }

        $cb = \Elasticsearch\ClientBuilder::create();
        if(count($hosts)) {
            $cb->setHosts($hosts);
        }
        $this->client = $cb->build();

        $cb = \Elasticsearch\ClientBuilder::create();
        if(count($index_hosts)) {
            $cb->setHosts($index_hosts);
        }
        $this->index_client = $cb->build();
    }

    /**
     * Get the ES client object.
     * @return \Elasticsearch\Elasticsearch
     */
    public function getClient() {
        return $this->client;
    }

    /**
     * Get the ES index client object.
     * @return \Elasticsearch\Elasticsearch
     */
    public function getIndexClient() {
        return $this->index_client;
    }

    public function process(\ECL\SymbolTable $table) {
        list($query_data, $query_settings) = $this->constructQuery($table, $this->query, $this->agg, $this->settings);

        $result_set = $this->query($query_data, $query_settings);
        $ret = $this->processResults($table, $result_set, $query_settings);

        return new \ECL\ResultSet($ret);
    }

    /**
     * Construct the query portion of the request body.
     */
    private function constructQuery(\ECL\SymbolTable $table, array $filters, \ECL\Command\Elasticsearch\Agg $agg=null, array $settings=[]) {
        foreach($settings as $key=>$val) {
            $settings[$key] = $table->resolve($val);
        }
        $from = \ECL\Util::get($settings, 'from', 'now-15m');
        $to = \ECL\Util::get($settings, 'to', 'now');

        $query_data = [
            'ignore_unavailable' => true,
            'body' => $this->constructQueryBody(
                $table, $filters, $agg,
                \ECL\Util::get($settings, 'fields'),
                \ECL\Util::get($settings, 'sort'),
                \ECL\Util::get($settings, 'size'),
                \ECL\Util::get($settings, 'date_field'),
                $from, $to
            )
        ];
        $query_settings = [
            'flatten' => \ECL\Util::get($settings, 'flatten', true),
            'scroll' => false,
            'count' => false,
        ];

        // Optionally set index.
        if(array_key_exists('index', $settings)) {
            $index = $settings['index'];

            if(\ECL\Util::get($settings, 'date_based', false)) {
                $index = implode(',', \ECL\Util::getIndices($index, $from, $to));
            }
            $query_data['index'] = $index;
        }

        return [$query_data, $query_settings];
    }

    /**
     * Construct the query body.
     */
    private function constructQueryBody(\ECL\SymbolTable $table, array $filters, \ECL\Command\Elasticsearch\Agg $agg=null, $fields=null, $sort=null, $size=null, $date_field=null, $from=null, $to=null) {
        $query_body = [
            'size' => 100,
            'query' => [
                'filtered' => [
                    'filter' => $this->constructFilter(
                        $table, $filters, $date_field, $from, $to
                    )
                ]
            ]
        ];

        // Optionally set aggregations
        if(!is_null($agg)) {
            $query_body['aggs'] = $agg->constructQuery($table);
            // When executing an agg, we don't care about the actual hits.
            $query_body['size'] = 0;
        }
        // Optionally set list of fields to return.
        if(!is_null($fields)) {
            $query_body['_source'] = ['include' => $fields];
        }

        // Optionally set sort order.
        if(!is_null($sort)) {
            $query_body['sort'] = array_map(function($x) { return [$x[0] => ['order' => $x[1] ? 'asc':'desc']]; }, $sort);
        }

        // Optionally set result set size.
        if(!is_null($size)) {
            $query_body['size'] = $size;
        }

        return $query_body;
    }

    /**
     * Construct the filters within the query.
     */
    private function constructFilter(\ECL\SymbolTable $table, $filters, $date_field, $from, $to) {
        $filters = $this->resolveFilter($table, $filters);

        // Optionally set date range filter.
        if(!is_null($date_field)) {
            $filter = ['range' => [$date_field => [
                'gte' => (new \DateTime('@' . $from))->format(\DateTime::ATOM),
                'lt' => (new \DateTime('@' . $to))->format(\DateTime::ATOM),
            ]]];
            if(count($filters) > 0) {
                $filters = ['and' => [$filters, $filter]];
            } else {
                $filters = $filter;
            }
        }

        return $filters;
    }

    private function resolveFilter(\ECL\SymbolTable $table, $node) {
        if(is_array($node)) {
            foreach($node as $k=>$entry) {
                $node[$k] = $this->resolveFilter($table, $entry);
            }
            return $node;
        }

        $ret = $table->resolve($node);
        if($node instanceof \ECL\Symbol && $node->getType() == \ECL\Symbol::T_LIST) {
            $id = time() . '_' . rand();
            $doc = [
                'entries' => $ret
            ];

            $response = $this->index_client->index([
                'index' => self::LUT_INDEX,
                'type' => self::LUT_TYPE,
                'id' => $id,
                'body' => $doc
            ]);

            $ret = [
                'index' => self::LUT_INDEX,
                'type' => self::LUT_TYPE,
                'id' => $id,
                'path' => self::LUT_PATH
            ];
        }
        return $ret;
    }

    /**
     * Send the query off to Elasticsearch and get the raw results back.
     * @param array $query_data The query body.
     * @param array $query_settings The query settings.
     * @return array Raw results.
     */
    public function query($query_data, $query_settings) {
        $result_set = [];

        if($query_settings['scroll']) {
            $cursor_lifetime = self::CUR_TTL;
            $query_data['scroll'] = '10s';
            $response = $this->client->search($query_data);

            $state = [];
            do {
                if(!array_key_exists('_scroll_id', $response)) {
                    throw new Exception('Scroll id not found');
                }

                $response = $this->client->scroll([
                    'scroll_id' => $response['_scroll_id'],
                    'scroll' => self::CUR_TTL,
                ]);
                $result_set[] = $response;
            } while(count($response['hits']['hits']) > 0);

            $this->client->clearScroll(['scroll_id' => $response['_scroll_id']]);
        } else {
            $result_set[] = $this->client->search($query_data);
        }

        return $result_set;
    }

    /**
     * Process raw results and return parsed results.
     */
    private function processResults(\ECL\SymbolTable $table, $result_set, $query_settings) {
        // If no agg, we're processing hits.
        if(is_null($this->agg)) {
            $results = [];
            $this->processHitResults($results, $result_set, $query_settings);
            return $results;
        }

        // Otherwise, process the agg!
        return $this->agg->processResults($table, \ECL\Util::get($result_set[0], 'aggregations', []));
    }

    /**
     * Process any hit results.
     */
    private function processHitResults(&$results, $result_set, $query_settings) {
        // If we're only returning hits, we can return the count here.
        if($query_settings['count']) {
            $results[] = ['count' => array_sum(array_map(function($x) { return $x['hits']['total']; }, $result_set))];
        }

        foreach($result_set as $result) {
            foreach($result['hits']['hits'] as $result) {
                $result = array_merge($result, $result['_source']);
                unset($result['_source']);
                if($query_settings['flatten']) {
                    $result = $this->flattenResults($result);
                }

                $results[] = $result;
            }
        }
    }

    /**
     * Flatten hit results.
     */
    private function flattenResults($results, $prefix=null) {
        if(!is_array($results)) {
            return [$prefix => $results];
        }

        $ret = [];
        foreach($results as $key=>$result) {
            // Flatten arrays.
            $sub_prefix = is_null($prefix) ? $key:"$prefix.$key";
            $ret = array_merge($ret, $this->flattenResults($result, $sub_prefix));
        }
        return $ret;
    }

    public static function escapeString($str) {
        return str_replace([
            '\\', '+', '-', '=', '&&', '||', '>', '<', '!', '(', ')',
            '{', '}', '[', ']', '^', '"', '~', '*', '?', ':',
            '/', ' '
        ], [
            '\\\\', '\\+', '\\-', '\\=', '\\&&', '\\||', '\\>', '\\<', '\\!', '\\(', '\\)',
            '\\{', '\\}', '\\[', '\\]', '\\^', '\\"', '\\~', '\\*', '\\?', '\\:',
            '\\/', '\\ '
        ], $str);
    }

    public static function escapeQuery($arr) {
        return implode('', array_map(function($x) {
            if(is_string($x)) {
                return self::escapeString($x);
            } else if($x == Elasticsearch\Token::W_STAR) {
                return '*';
            } else if ($x == Elasticsearch\Token::W_QMARK) {
                return '?';
            }
        }, $arr));
    }
}
