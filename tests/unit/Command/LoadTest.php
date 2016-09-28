<?php

class LoadTest extends PHPUnit_Framework_TestCase {
    public function testLoad() {
        $symtab = new \ECL\SymbolTable;
        $symtab['a'] = new \ECL\ResultSet([['a' => 1]]);
        $symtab['b'] = new \ECL\ResultSet([['b' => 1]]);
        $symtab['c'] = new \ECL\ResultSet([['b' => 2]]);
        $command = new \ECL\Command\Load(['a', 'b', 'c']);
        $res = $command->process($symtab);

        $this->assertSame($res->getKeys(), ['a', 'b']);
        $this->assertSame($res->getAll(), [['a' => 1], ['b' => 1], ['b' => 2]]);
    }
}
