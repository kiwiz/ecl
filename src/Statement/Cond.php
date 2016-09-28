<?php

namespace ECL\Statement;

/**
 * Cond statement
 * Branches control based on the result of an expression.
 */
class Cond extends \ECL\Statement {
    /** @var \ECL\Symbol|string The expression string to use. */
    private $expr = null;
    /** @var Statement[] Branch to execute if expression is true. */
    private $pos_case = [];
    /** @var Statement[] Branch to execute if expression is false. */
    private $neg_case = [];

    /**
     * @param \ECL\Symbol|string SEL expression
     * @param Statement[] $pos_case Positive branch
     * @param Statement[] $pos_case Negative branch
     */
    public function __construct($expr, $pos_case, $neg_case=null) {
        $this->expr = $expr;
        $this->pos_case = $pos_case;
        $this->neg_case = $neg_case;
    }

    public function process(\ECL\SymbolTable $table) {
        $expr = $table->resolve($this->expr, \ECL\Symbol::T_STR);

        $el = new \ECL\ExpressionLanguage;
        $statementlist = [];
        if($el->evaluate($expr, $table)) {
            $statementlist = $this->pos_case;
        } elseif(!is_null($this->neg_case)) {
            $statementlist = $this->neg_case;
        }

        $results = [];
        // Loop over every Statement and execute it.
        foreach($statementlist as $statement) {
            $results = array_merge(
                $results,
                (array) $statement->process($table)
            );
        }

        return $results;
    }

    public function optimize() {}
}
