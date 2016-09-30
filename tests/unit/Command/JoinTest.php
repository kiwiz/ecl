<?php

class JoinTest extends PHPUnit_Framework_TestCase {
    public function testMatch() {
        $symtab = Helper::getSymbolTable([
            ['a' => '1', 'b' => '2', 'c' => 'x'],
        ]);
        $symtab['a'] = new \ECL\ResultSet([
            ['a' => '1', 'b' => '2', 'c' => '3'],
        ]);
        $command = new \ECL\Command\Join('a', ['a', 'b'], \ECL\Command\Join::T_INNER);
        $res = $command->process($symtab);

        $expected = [
            ['a' => '1', 'b' => '2', 'c' => '3'],
        ];
        $this->assertSame($expected, $res->getAll());
    }

    public function testMulti() {
        $symtab = Helper::getSymbolTable([
            ['a' => '1', 'b' => '2', 'c' => 'x'],
        ]);
        $symtab['a'] = new \ECL\ResultSet([
            ['a' => '1', 'b' => '2', 'c' => '3'],
            ['a' => '1', 'b' => '2', 'c' => '4'],
        ]);
        $command = new \ECL\Command\Join('a', ['a', 'b'], \ECL\Command\Join::T_INNER);
        $res = $command->process($symtab);

        $expected = [
            ['a' => '1', 'b' => '2', 'c' => '3'],
            ['a' => '1', 'b' => '2', 'c' => '4'],
        ];
        $this->assertSame($expected, $res->getAll());
    }

    public function testReverse() {
        $symtab = Helper::getSymbolTable([
            ['a' => '1', 'b' => '2', 'c' => '3'],
            ['a' => '1', 'b' => '2', 'c' => '4'],
        ]);
        $symtab['a'] = new \ECL\ResultSet([
            ['a' => '1', 'b' => '2', 'c' => 'x'],
        ]);
        $command = new \ECL\Command\Join('a', ['a', 'b'], \ECL\Command\Join::T_INNER);
        $res = $command->process($symtab);

        $expected = [
            ['a' => '1', 'b' => '2', 'c' => 'x'],
        ];
        $this->assertSame($expected, $res->getAll());
    }

    public function testNoMatch() {
        $symtab = Helper::getSymbolTable([
            ['a' => '1', 'b' => '2'],
        ]);
        $symtab['a'] = new \ECL\ResultSet([
            ['a' => '2', 'b' => '2'],
        ]);
        $command = new \ECL\Command\Join('a', ['a', 'b'], \ECL\Command\Join::T_INNER);
        $res = $command->process($symtab);

        $expected = [];
        $this->assertSame($expected, $res->getAll());
    }

    public function testLeft() {
        $symtab = Helper::getSymbolTable([
            ['a' => '3'],
        ]);
        $symtab['a'] = new \ECL\ResultSet([
            ['a' => '2', 'b' => '2'],
        ]);
        $command = new \ECL\Command\Join('a', ['a'], \ECL\Command\Join::T_LEFT);
        $res = $command->process($symtab);

        $expected = [
            ['a' => '3'],
        ];
        $this->assertSame($expected, $res->getAll());
    }

    public function testRight() {
        $symtab = Helper::getSymbolTable([
            ['a' => '2', 'b' => '2'],
        ]);
        $symtab['a'] = new \ECL\ResultSet([
            ['a' => '3'],
        ]);
        $command = new \ECL\Command\Join('a', ['a'], \ECL\Command\Join::T_RIGHT);
        $res = $command->process($symtab);

        $expected = [
            ['a' => '3'],
        ];
        $this->assertSame($expected, $res->getAll());
    }
}
