<?php

namespace ECL\Command\Sort;

/**
 * Sort\Expr command
 * Sort rows via a given SEL expression.
 */
class Expr extends \ECL\Command\Sort {
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
        $ret = $result->getAll();

        $el = new \ECL\ExpressionLanguage;
        usort($ret, function($a, $b) use ($el, $expr) {
            return (int) $el->evaluate($expr, ['a' => $a, 'b' => $b]);
        });

        return new \ECL\ResultSet($ret);
    }
}
