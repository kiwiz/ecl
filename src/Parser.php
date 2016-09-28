<?php

namespace ECL;

/**
 * Class Parser
 * Query parser.
 * @package ECL
 */
class Parser extends InternalParser {
    protected $es_builder = null;

    public function __construct() {
        $this->es_builder = new \ECL\Command\Elasticsearch\Builder;
    }

    public function setESBuilder(Command\Elasticsearch\Builder $builder) {
        $this->es_builder = $builder;
    }
}
