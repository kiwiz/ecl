<?php

class boop {

    /**
     * Call the list provider to retrieve the contents of the list.
     * @param string $key List name.
     * @return array An array of entries.
     */
    private function getList($key) {
        return !is_null($this->list_provider) ? call_user_func($this->list_provider, $key):[];
    }

    public function getConnection() {
        $host = \ECL\Util::get($this->settings, 'host');
        if(!is_null($this->conn_provider)) {
            return call_user_func($this->conn_provider, $host);
        } else {
            $cb = \Elasticsearch\ClientBuilder::create();
            if(!is_null($host)) {
                $cb->setHosts([$host]);
            }
            return $cb->build();
        }
    }

    private function index($field, $arr, $inline) {
        if($inline) {
            if(count($arr) > 1000) {
                throw new ElasticException('Too many entries in list');
            }
            return ['query' => [
                'query_string' => [
                    'default_field' => $field,
                    'query' => implode(' OR ', array_map(['\ESQuery\Util', 'escapeGroup'], $arr))
                ]
            ]];
        } else {
            // Generate a lookup table.
            $doc = [
                '_ttl' => '1m',
                'table' => $arr
            ];
            $id = time() . '_' . rand();

            $response = $this->client->index([
                'index' => 'lookup_tables',
                'type' => 'ecl_lookup',
                'id' => $id,
                'body' => $doc
            ]);

            return ['terms'=> [$field => [
                'index' => 'lookup_tables',
                'type' => 'ecl_lookup',
                'id' => $id,
                'path' => 'table'
            ]]];
        }
    }

    private function resolveFilter(\ECL\SymbolTable $table, $node) {
        if(is_array($node)) {
            foreach($node as $k=>$entry) {
                $node[$k] = $this->resolveFilter($table, $entry);
            }
            return $node;
        }
        return $table->resolve($node);
    }
}
