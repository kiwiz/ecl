<?php

class CondTest extends PHPUnit_Framework_TestCase {
    public function testTrue() {
        $symtab = Helper::getSymbolTable([['a' => 'b']]);
        $command = new \ECL\Statement\Cond('1', [new \ECL\Statement\Set('a', 1)]);
        $res = $command->process($symtab);

        $this->assertSame(1, $symtab['a']);
    }

    public function testFalse() {
        $symtab = Helper::getSymbolTable([['a' => 'b']]);
        $command = new \ECL\Statement\Cond('0', [], [new \ECL\Statement\Set('a', 1)]);
        $res = $command->process($symtab);

        $this->assertSame(1, $symtab['a']);
    }
}
