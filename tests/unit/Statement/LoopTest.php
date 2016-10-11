<?php

class LoopTest extends PHPUnit_Framework_TestCase {
    public function testLoop() {
        $symtab = Helper::getSymbolTable([['a' => 'b'], ['c' => 'd']]);
        $statement = new \ECL\Statement\Loop('_', [new \ECL\Statement\CommandList([
            new \ECL\Command\Count()
        ])]);
        $res = $statement->process($symtab);

        $this->assertCount(2, $res);
    }
}
