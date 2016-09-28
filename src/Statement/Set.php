<?php

namespace ECL\Statement;

/**
 * Set statement
 * Writes a value into the SymbolTable.
 */
class Set extends \ECL\Statement{
    /** @var string The symbol to write to. */
    private $target = null;
    /** @var mixed The value to write. */
    private $value = null;

    /**
     * @param string $target Symbol name.
     * @param mixed $value Value.
     */
    public function __construct($target, $value) {
        $this->target = $target;
        $this->value = $value;
    }

    public function process(\ECL\SymbolTable $table) {
        $table[$this->target] = $this->value;

        return [];
    }

    public function optimize() {}
}
