<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Test\Unit\Model\DeliveryMatrix;

use Paazl\CheckoutWidget\Model\DeliveryMatrix\Letter;
use Paazl\CheckoutWidget\Test\Unit\UnitTestCase;

class LetterTest extends UnitTestCase
{
    /**
     * @var Letter
     */
    private $letter;

    protected function setUpWithoutVoid()
    {
        $this->letter = new Letter();
    }

    /**
     * @param string|null $value
     * @param bool $expected
     * @dataProvider isValidDataProvider
     */
    public function testIsValid($value, $expected)
    {
        $this->assertSame($expected, $this->letter->isValid($value));
    }

    /**
     * @return array[]
     */
    public function isValidDataProvider()
    {
        return [
            ['A', true],
            ['Z', true],
            ['AA', true],
            ['ZZ', true],
            ['a', false],
            ['AAA', false],
            ['1', false],
            ['', false],
            [null, false],
            [' A', false],
        ];
    }

    /**
     * @param string $a
     * @param string $b
     * @param int $expected
     * @dataProvider compareDataProvider
     */
    public function testCompare($a, $b, $expected)
    {
        $this->assertSame($expected, $this->letter->compare($a, $b) <=> 0);
    }

    /**
     * @return array[]
     */
    public function compareDataProvider()
    {
        return [
            'E after D' => ['E', 'D', 1],
            'D before E' => ['D', 'E', -1],
            'equal' => ['C', 'C', 0],
            'AA after Z' => ['AA', 'Z', 1],
            'Z before AA' => ['Z', 'AA', -1],
            'AB after AA' => ['AB', 'AA', 1],
        ];
    }

    /**
     * @param array $letters
     * @param string|null $expected
     * @dataProvider highestDataProvider
     */
    public function testHighest(array $letters, $expected)
    {
        $this->assertSame($expected, $this->letter->highest($letters));
    }

    /**
     * @return array[]
     */
    public function highestDataProvider()
    {
        return [
            'last letter wins' => [['D', 'E'], 'E'],
            'order does not matter' => [['E', 'B', 'D'], 'E'],
            'two letters outrank one' => [['Z', 'AA', 'B'], 'AA'],
            'input is normalized' => [[' d ', 'e'], 'E'],
            'invalid letters are ignored' => [['D', 'E1', 'AAA', ''], 'D'],
            'nothing valid' => [['', 'abc', '1'], null],
            'empty' => [[], null],
        ];
    }

    public function testNormalize()
    {
        $this->assertSame('AB', $this->letter->normalize(' ab '));
        $this->assertSame('', $this->letter->normalize(null));
    }
}
