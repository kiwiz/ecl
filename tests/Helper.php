<?php

class Helper {
    public static function getSymbolTable($data) {
        $symtab = new \ECL\SymbolTable;
        $symtab[\ECL\SymbolTable::DEFAULT_SYMBOL] = new \ECL\ResultSet($data);

        return $symtab;
    }
}
