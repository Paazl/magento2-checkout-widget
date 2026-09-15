<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Test\Unit\Model\System\Config\Backend;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Paazl\CheckoutWidget\Model\DeliveryMatrix\Letter;
use Paazl\CheckoutWidget\Model\System\Config\Backend\CustomerTagMatrix;
use Paazl\CheckoutWidget\Test\Unit\UnitTestCase;

class CustomerTagMatrixTest extends UnitTestCase
{
    /**
     * @var CustomerTagMatrix
     */
    private $backend;

    protected function setUpWithoutVoid()
    {
        $this->backend = $this->objectManager->getObject(CustomerTagMatrix::class, [
            'letter' => new Letter(),
            'serializer' => new Json(),
        ]);
    }

    public function testRowsAreNormalizedAndSerialized()
    {
        $this->backend->setValue([
            '__empty' => '',
            '_1' => ['tag' => ' VIP ', 'matrix_letter' => ' b '],
            '_2' => ['tag' => 'dhl1', 'matrix_letter' => 'AA'],
        ]);

        $this->backend->beforeSave();

        $this->assertSame(
            '{"_1":{"tag":"VIP","matrix_letter":"B"},"_2":{"tag":"dhl1","matrix_letter":"AA"}}',
            $this->backend->getValue()
        );
    }

    public function testEmptyTableIsAllowed()
    {
        $this->backend->setValue(['__empty' => '']);
        $this->backend->beforeSave();

        $this->assertSame('[]', $this->backend->getValue());
    }

    /**
     * @param array $rows
     * @param string $messagePart
     * @dataProvider invalidRowsDataProvider
     */
    public function testInvalidRowsAreRejected(array $rows, $messagePart)
    {
        $this->backend->setValue($rows);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage($messagePart);

        $this->backend->beforeSave();
    }

    /**
     * @return array[]
     */
    public function invalidRowsDataProvider()
    {
        return [
            'empty tag' => [
                ['_1' => ['tag' => '  ', 'matrix_letter' => 'B']],
                'customer tag cannot be empty',
            ],
            'three letters' => [
                ['_1' => ['tag' => 'vip', 'matrix_letter' => 'ABC']],
                'not a valid matrix position',
            ],
            'digit' => [
                ['_1' => ['tag' => 'vip', 'matrix_letter' => '1']],
                'not a valid matrix position',
            ],
            'duplicate tag, different case' => [
                [
                    '_1' => ['tag' => 'vip', 'matrix_letter' => 'B'],
                    '_2' => ['tag' => 'VIP', 'matrix_letter' => 'C'],
                ],
                'mapped more than once',
            ],
        ];
    }
}
