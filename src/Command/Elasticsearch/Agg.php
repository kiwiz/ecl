<?php

namespace ECL\Command\Elasticsearch;

abstract class Agg {
    /** @var string The key to aggregate over. */
    protected $key = null;
    /** @var array Aggregation options. */
    protected $options = [];
    /** @var \ECL\Command\Elasticsearch\Agg|null Nested Agg instance. */
    protected $agg = null;

    const TYPE = null;

    /**
     * @param string $key The key to aggregate over.
     * @param array $options Aggregation options.
     * @param \ECL\Command\Elasticsearch\Agg $agg Sub aggregation.
     */
    public function __construct($key, array $options=[], Agg $agg=null) {
        $this->key = $key;
        $this->options = $options;
        $this->agg = $agg;
    }

    public function constructQuery(\ECL\SymbolTable $table) {
        $key = $table->resolve($this->key, \ECL\Symbol::T_STR);
        $query_key = '$_' . $key;

        $options = array_merge($this->options, ['field' => $key]);
        $agg = [static::TYPE => $options];

        if(!is_null($this->agg)) {
            $agg['aggs'] = $this->agg->constructQuery($table);
        }

        return [$query_key => $agg];
    }

    abstract public function processResults(\ECL\SymbolTable $table, array $results);
}
