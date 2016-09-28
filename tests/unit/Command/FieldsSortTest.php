<?php

class FieldsSortTest extends PHPUnit_Framework_TestCase {
    public function testSortNone() {
        $command = new \ECL\Command\Sort\Fields([]);
        $res = $command->process(Helper::getSymbolTable([
            ['a' => 1],
            ['a' => 3],
            ['a' => 2],
        ]));

        $expected = [
            ['a' => 1],
            ['a' => 3],
            ['a' => 2],
        ];
        $this->assertSame($expected, $res->getAll());
    }

    public function testSortOne() {
        $command = new \ECL\Command\Sort\Fields([['a', \ECL\Command\Sort\Fields::T_DESC]]);
        $res = $command->process(Helper::getSymbolTable([
            ['a' => 3, 'b' => 2],
            ['a' => 99],
            ['a' => 1, 'b' => 3],
            ['b' => 2],
        ]));

        $expected = [
            ['a' => 99],
            ['a' => 3, 'b' => 2],
            ['a' => 1, 'b' => 3],
            ['b' => 2],
        ];
        $this->assertSame($expected, $res->getAll());
    }

    public function testSortTwo() {
        $command = new \ECL\Command\Sort\Fields([
            ['a', \ECL\Command\Sort\Fields::T_DESC],
            ['b', \ECL\Command\Sort\Fields::T_ASC],
        ]);
        $res = $command->process(Helper::getSymbolTable([
            ['a' => 3, 'b' => 2],
            ['a' => 3, 'b' => 3],
            ['a' => 99],
            ['a' => 1, 'b' => 3],
            ['b' => 2],
        ]));

        $expected = [
            ['a' => 99],
            ['a' => 3, 'b' => 2],
            ['a' => 3, 'b' => 3],
            ['a' => 1, 'b' => 3],
            ['b' => 2],
        ];
        $this->assertSame($expected, $res->getAll());
    }
}
