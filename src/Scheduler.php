<?php

namespace ECL;

/**
 * Class Scheduler
 * Execute an ECL program.
 * @package ECL
 */
class Scheduler {
    /**
     * Main entrypoint.
     * @param Statement[] $statementlist A list of Statements for execution.
     * @return ResultSet[] The list of results.
     */
    public function process(array $statementlist, SymbolTable $table=null) {
        if(is_null($table)) {
            $table = new SymbolTable;
        }
        $statementlist = $this->optimize($statementlist);

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

    /**
     * Optimize the list of Statements.
     * @param Statement[] $statementlist A list of Statements for execution.
     * @return array An optimized list.
     */
    private function optimize(array $statementlist) {
        foreach($statementlist as $statement) {
            $statement->optimize();
        }
        return $statementlist;
    }
}
