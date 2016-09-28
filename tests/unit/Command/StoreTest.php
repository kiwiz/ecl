<?php

class StoreTest extends PHPUnit_Framework_TestCase {
    public function testStore() {
        $symtab = Helper::getSymbolTable([]);
        $command = new \ECL\Command\Store('a');
        $res = $command->process($symtab);

        $this->assertSame($symtab[\ECL\SymbolTable::DEFAULT_SYMBOL], $symtab['a']);
    }
}
