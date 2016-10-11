<?php

class CommandListTest extends PHPUnit_Framework_TestCase {
    public function testCommandList() {
        $symtab = Helper::getSymbolTable([['a' => 'b']]);
        $statement = new \ECL\Statement\CommandList([new \ECL\Command\Count]);
        $res = $statement->process($symtab);

        $this->assertSame([['count' => 1]], $res[0]->getAll());
    }
}
