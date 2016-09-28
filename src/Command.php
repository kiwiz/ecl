<?php

namespace ECL {
    /**
     * Class Command
     * Base ECL Command.
     * @package ECL
     */
    abstract class Command {
        /**
         * Process the command.
         * @param SymbolTable $table The global SymbolTable.
         * @return ResultSet
         */
        abstract public function process(SymbolTable $table);
    }
}

namespace ECL\Command {
    class Exception extends \ECL\Exception {}
}
