<?php

class ExprSortTest extends PHPUnit_Framework_TestCase {
    public function testExpr() {
        $command = new \ECL\Command\Sort\Expr('strcmp(a["a"], b["a"])');
        $res = $command->process(Helper::getSymbolTable([
            ['a' => 4],
            ['a' => 1],
            ['a' => 3],
            ['a' => 2],
        ]));

        $expected = [
            ['a' => 1],
            ['a' => 2],
            ['a' => 3],
            ['a' => 4],
        ];
        $this->assertSame($expected, $res->getAll());
    }
}
