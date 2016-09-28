<?php

namespace ECL\Command\Elasticsearch\Agg;

/**
 * Bucket type Agg
 */
class Bucket extends \ECL\Command\Elasticsearch\Agg {
    public function processResults(\ECL\SymbolTable $table, array $results) {
        $key = $table->resolve($this->key, \ECL\Symbol::T_STR);
        $query_key = '$_' . $key;
        if(!array_key_exists($query_key, $results)) {
            return [];
        }

        $buckets = \ECL\Util::get($results[$query_key], 'buckets', []);

        $ret = [];
        foreach($buckets as $bucket) {
            $partial = [$key => $bucket['key']];
            if(!is_null($this->agg)) {
                $sub_ret = $this->agg->processResults($table, $bucket);
                foreach($sub_ret as $row) {
                    $ret[] = array_merge($partial, $row);
                }
            } else {
                $partial['count'] = $bucket['doc_count'];
                $ret[] = $partial;
            }
        }

        return $ret;
    }
}
