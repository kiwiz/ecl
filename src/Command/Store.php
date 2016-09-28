<?php

namespace ECL\Command;

/**
 * Store command
 * Saves the results of the previous command into the SymbolTable.
 */
class Store extends \ECL\Command {
    /** @var string The symbol to write to. */
    private $target = null;

    /**
     * @param string $target Symbol name.
     */
    public function __construct($target) {
        $this->target = $target;
    }

    public function process(\ECL\SymbolTable $table) {
        $result = $table[\ECL\SymbolTable::DEFAULT_SYMBOL];
        $table[$this->target] = $result;

        return $result;
    }
}
