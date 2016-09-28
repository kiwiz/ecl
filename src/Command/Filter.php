<?php

namespace ECL\Command;

/**
 * Filter command
 * Filter out entries that don't match the given SEL expression.
 */
class Filter extends \ECL\Command {
    /** @var \ECL\Symbol|string The expression string to use. */
    private $expr = null;

    /**
     * @param \ECL\Symbol|string $expr Expr.
     */
    public function __construct($expr) {
        $this->expr = $expr;
    }

    public function process(\ECL\SymbolTable $table) {
        $result = $table[\ECL\SymbolTable::DEFAULT_SYMBOL];
        $expr = $table->resolve($this->expr, \ECL\Symbol::T_STR);
        $ret = [];

        $el = new \ECL\ExpressionLanguage;
        foreach($result->getAll() as $entry) {
            if($el->evaluate($expr, ['_' => $entry])) {
                $ret[] = $entry;
            }
        }

        return new \ECL\ResultSet($ret);
    }
}
