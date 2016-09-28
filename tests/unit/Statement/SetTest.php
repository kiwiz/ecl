<?php

class SetTest extends PHPUnit_Framework_TestCase {
    public function testSet() {
        $symtab = Helper::getSymbolTable([]);
        $command = new \ECL\Statement\Set('a', 999);
        $res = $command->process($symtab);

        $this->assertSame(999, $symtab['a']);
    }
}
