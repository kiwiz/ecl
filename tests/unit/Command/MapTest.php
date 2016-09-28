<?php

class MapTest extends PHPUnit_Framework_TestCase {
    public function testCombine() {
        $command = new \ECL\Command\Map([
            [\ECL\Command\Map::T_COMBINE, ['a', 'b', 'e'], 'x'],
            [\ECL\Command\Map::T_COMBINE, ['c', 'd'], 'y'],
        ]);
        $res = $command->process(Helper::getSymbolTable([
            ['a' => '1', 'b' => '2', 'c' => '3', 'd' => '4'],
        ]));

        $expected = [
            ['a' => '1', 'b' => '2', 'c' => '3', 'd' => '4', 'x' => '12', 'y' => '34'],
        ];
        $this->assertSame($expected, $res->getAll());
    }

    public function testDelete() {
        $command = new \ECL\Command\Map([
            [\ECL\Command\Map::T_DELETE, 'x'],
            [\ECL\Command\Map::T_DELETE, 'y'],
        ]);
        $res = $command->process(Helper::getSymbolTable([
            ['a' => '1', 'b' => '2', 'x' => '3', 'd' => '4'],
        ]));

        $expected = [
            ['a' => '1', 'b' => '2', 'd' => '4'],
        ];
        $this->assertSame($expected, $res->getAll());
    }

    public function testExpr() {
        $command = new \ECL\Command\Map([
            [\ECL\Command\Map::T_EXPR, 'x', '"1"'],
            [\ECL\Command\Map::T_EXPR, 'y', '"2"'],
        ]);
        $res = $command->process(Helper::getSymbolTable([
            ['a' => '1', 'x' => '3', 'd' => '4'],
        ]));

        $expected = [
            ['a' => '1', 'x' => '1', 'd' => '4'],
        ];
        $this->assertSame($expected, $res->getAll());
    }
}
