<?php

namespace ECL\Command;

/**
 * Head command
 * Returns the first n entries in the result.
 */
class Head extends \ECL\Command {
    /** @var \ECL\Symbol|int The number of results to return. */
    private $count = null;

    /**
     * @param \ECL\Symbol|int $count Count.
     */
    public function __construct($count) {
        $this->count = $count;
    }

    public function process(\ECL\SymbolTable $table) {
        $result = $table[\ECL\SymbolTable::DEFAULT_SYMBOL];
        $count = $table->resolve($this->count, \ECL\Symbol::T_INT);

        return new \ECL\ResultSet(array_slice($result->getAll(), 0, $count));
    }
}
