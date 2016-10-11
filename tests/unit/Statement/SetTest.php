<?php

class SetTest extends PHPUnit_Framework_TestCase {
    public function testSet() {
        $symtab = Helper::getSymbolTable([]);
        $statement = new \ECL\Statement\Set('a', 999);
        $res = $statement->process($symtab);

        $this->assertSame(999, $symtab['a']);
    }
}
