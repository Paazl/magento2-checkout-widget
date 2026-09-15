<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Paazl\CheckoutWidget\Model\Config;
use Paazl\CheckoutWidget\Test\Unit\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ConfigTest extends UnitTestCase
{

    /**
     * @var object
     */
    private $entity;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    protected function setUpWithoutVoid()
    {
        /** @var ScopeConfigInterface|MockObject $scopeConfigMock */
        $scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->scopeConfigMock = $scopeConfigMock;

        $this->entity = $this->objectManager->getObject(
            Config::class,
            ['scopeConfig' => $scopeConfigMock, 'serializer' => new Json()]
        );
    }

    /**
     * @param $scopeValue
     * @param $scopeStore
     * @param $expected
     * @dataProvider getInsuranceValueDataProvider
     */
    public function testGetInsuranceValue($scopeValue, $scopeStore, $expected)
    {
        $this->scopeConfigMock->method('getValue')->willReturn($scopeValue);

        $this->assertEquals($expected, $this->entity->getInsuranceValue($scopeStore));
    }

    /**
     * @param mixed $scopeValue
     * @param array $expected
     * @dataProvider getCustomerTagMatrixDataProvider
     */
    public function testGetCustomerTagMatrix($scopeValue, array $expected)
    {
        $this->scopeConfigMock->method('getValue')->willReturn($scopeValue);

        $this->assertSame($expected, $this->entity->getCustomerTagMatrix());
    }

    /**
     * @return array[]
     */
    public function getCustomerTagMatrixDataProvider()
    {
        return [
            'not configured' => [null, []],
            'empty string' => ['', []],
            'invalid json' => ['{not json', []],
            'keyed by lower-cased tag, letter upper-cased' => [
                '{"_1":{"tag":"VIP","matrix_letter":"b"},"_2":{"tag":"dhl1","matrix_letter":"D"}}',
                ['vip' => 'B', 'dhl1' => 'D'],
            ],
            'incomplete rows are skipped' => [
                '{"_1":{"tag":"","matrix_letter":"B"},"_2":{"tag":"x"},"_3":{"tag":"y","matrix_letter":"E"}}',
                ['y' => 'E'],
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function getInsuranceValueDataProvider()
    {
        return [
            [null, 'code', 0],
            ['null', 'code2', 0],
            ['-1', 'code3', 1],
            ['123.3349847', 'code', 123.3349847],
        ];
    }
}
