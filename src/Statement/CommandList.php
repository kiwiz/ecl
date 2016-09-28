<?php

namespace ECL\Statement;

/**
 * CommandList statement
 * Executes a sequence of Commands.
 */
class CommandList extends \ECL\Statement {
    /** @var Command[] The list of Commands to execute. */
    private $commands = [];

    /**
     * @param Command[] $commands The list of Commands.
     */
    public function __construct($commands) {
        $this->commands = $commands;
    }

    /**
     * Process the list of Commands.
     * @param SymbolTable $table The global SymbolTable.
     * @return ResultSet[] The result from the last Command or an empty array.
     */
    public function process(\ECL\SymbolTable $table) {
        $results = [];

        // Loop over every Command and process it.
        for($i = 0; $i < count($this->commands); ++$i) {
            $command = $this->commands[$i];
            $curr_result = $command->process($table);
            $table[\ECL\SymbolTable::DEFAULT_SYMBOL] = $curr_result;

            // If the last Command is not a store, allow returning the result.
            if($i + 1 >= count($this->commands) && !($command instanceof \ECL\Command\Store)) {
                $results[] = $curr_result;
            }
        }

        // Results from the last Command are only available within this CommandList.
        unset($table[\ECL\SymbolTable::DEFAULT_SYMBOL]);

        return $results;
    }

    public function optimize() {
    }
}
