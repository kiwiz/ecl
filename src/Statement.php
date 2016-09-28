<?php

namespace ECL;

/**
 * Class Statement
 * Base ECL Statement.
 * @package ECL
 */
abstract class Statement {
    /**
     * Process the statement.
     * @param SymbolTable $table The global SymbolTable.
     * @return ResultSet[]
     */
    abstract public function process(SymbolTable $table);
    abstract public function optimize();
}
