<?php

namespace ECL\Command;

/**
 * Count command
 * Returns a count of results.
 */
class Count extends \ECL\Command {
    public function process(\ECL\SymbolTable $table) {
        $result = $table[\ECL\SymbolTable::DEFAULT_SYMBOL];

        return new \ECL\ResultSet([['count' => count($result->getAll())]]);
    }
}
