<?php

namespace ECL\Command\Elasticsearch\Agg;

/**
 * Metrics type Agg
 */
class Metrics extends \ECL\Command\Elasticsearch\Agg {
    public function processResults(\ECL\SymbolTable $table, array $results) {
        $key = $table->resolve($this->key, \ECL\Symbol::T_STR);
        $query_key = '$_' . $key;
        if(!array_key_exists($query_key, $results)) {
            return [];
        }

        return [
            [$key => $results[$query_key]['value']]
        ];
    }
}
