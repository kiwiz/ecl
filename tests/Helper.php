<?php

require_once(realpath(__DIR__ . '/../vendor/autoload.php'));

class Helper {
    public static function getSymbolTable($data) {
        $symtab = new \ECL\SymbolTable;
        $symtab[\ECL\SymbolTable::DEFAULT_SYMBOL] = new \ECL\ResultSet($data);

        return $symtab;
    }

    public static function invokeMethod(&$object, $methodName, array $parameters=[]) {
        $reflection = new \ReflectionClass(is_string($object) ? $object:get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($method->isStatic() ? null:$object, $parameters);
    }
}
