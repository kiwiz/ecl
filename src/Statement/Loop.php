<?php

namespace ECL\Statement;

/**
 * Loop statement
 * Iterate over a result and execute a code block.
 */
class Loop extends \ECL\Statement {
    /** @var string The symbol to loop over. */
    private $source = null;
    /** @var \ECL\Statement[] Block to execute. */
    private $statements = [];

    /**
     * @param string $source Source name.
     * @param \ECL\Statement[] $statements Code block to execute.
     */
    public function __construct($source, $statements) {
        $this->source = $source;
        $this->statements = $statements;
    }

    public function process(\ECL\SymbolTable $table) {
        $result = $table->resolve(new \ECL\Symbol($this->source), \ECL\Symbol::T_RES);

        $results = [];
        foreach($result->getAll() as $row) {
            $table['_'] = new \ECL\ResultSet([$row]);
            // Loop over every Statement and execute it.
            foreach($this->statements as $statement) {
                $results = array_merge(
                    $results,
                    (array) $statement->process($table)
                );
            }
        }

        return $results;
    }

    public function optimize() {}
}
