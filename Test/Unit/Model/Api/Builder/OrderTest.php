<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Test\Unit\Model\Api\Builder;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Address;
use Paazl\CheckoutWidget\Api\Data\Order\OrderReferenceInterface;
use Paazl\CheckoutWidget\Model\Api\Builder\Order;
use Paazl\CheckoutWidget\Model\Api\Builder\Reference;
use Paazl\CheckoutWidget\Model\Config;
use Paazl\CheckoutWidget\Model\ExtInfoHandler;
use Paazl\CheckoutWidget\Model\Handler\Item;
use Paazl\CheckoutWidget\Model\ResourceModel\Order\OrderReferenceRepository;
use Paazl\CheckoutWidget\Model\ShippingInfo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{

    /**
     * @var ObjectManagerHelper
     */
    private $objectManager;

    /**
     * @var Order
     */
    private $entity;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $extInfoHandlerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $orderReferenceRepositoryMock;

    /**
     * @var MockObject
     */
    private $configMock;

    protected function setUp()
    {
        $this->objectManager = (new ObjectManagerHelper($this));

        $extInfoHandlerMock = $this->getMockBuilder(ExtInfoHandler::class)->disableOriginalConstructor()->getMock();
        $this->extInfoHandlerMock = $extInfoHandlerMock;

        $referenceBuilderMock = $this->getMockBuilder(Reference::class)->disableOriginalConstructor()->getMock();
        $configMock = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $this->configMock = $configMock;

        $orderReferenceRepositoryMock = $this->getMockBuilder(OrderReferenceRepository::class)
            ->disableOriginalConstructor()->getMock();
        $this->orderReferenceRepositoryMock = $orderReferenceRepositoryMock;

        $itemHandlerMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()->getMock();

        $this->entity = $this->objectManager->getObject(
            Order::class,
            [
                'extInfoHandler'           => $extInfoHandlerMock,
                'referenceBuilder'         => $referenceBuilderMock,
                'config'                   => $configMock,
                'orderReferenceRepository' => $orderReferenceRepositoryMock,
                'itemHandler'              => $itemHandlerMock
            ]
        );
    }

    /**
     * @param array $street
     * @param array $expect
     *
     * @dataProvider parseAddressProvider
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testParseAddress(array $street, array $expect)
    {
        $addressMock = $this->getMockBuilder(Address::class)->disableOriginalConstructor()->getMock();
        $addressMock->expects($this->once())->method('getStreet')->willReturn($street);

        $result = $this->entity->parseAddress($addressMock);

        $this->assertEquals($expect, $result);
    }

    public function parseAddressProvider(): array
    {
        return [
            [
                ['Baker str', '221', 'b'],
                ['street' => 'Baker str', 'houseNumber' => '221', 'houseNumberExtension' => 'b']
            ],
            [
                ['Baker str 221b', ' ', ' '],
                ['street' => 'Baker str', 'houseNumber' => '221', 'houseNumberExtension' => 'b']
            ],
            [
                ['Donkere spaarne ', '52', ''],
                ['street' => 'Donkere spaarne', 'houseNumber' => '52', 'houseNumberExtension' => '']
            ],
            [
                ['Donkere spaarne ', ' 52 -', 'HN'],
                ['street' => 'Donkere spaarne', 'houseNumber' => '52', 'houseNumberExtension' => 'HN']
            ],
            [
                ['1740 casey street'],
                ['street' => 'casey street', 'houseNumber' => '1740', 'houseNumberExtension' => '']
            ],
            [
                ['Проспект Гагарина 178Д'],
                ['street' => 'Проспект Гагарина', 'houseNumber' => '178', 'houseNumberExtension' => 'Д']
            ],
            [
                ['Oberländer Ufer 192'],
                ['street' => 'Oberländer Ufer', 'houseNumber' => '192', 'houseNumberExtension' => '']
            ],
            [
                ['Donkere spaarne  68 / HM'],
                ['street' => 'Donkere spaarne', 'houseNumber' => '68', 'houseNumberExtension' => 'HM']
            ],
            [
                [' 68 / HM  Donkere spaarne  '],
                ['street' => 'Donkere spaarne', 'houseNumber' => '68', 'houseNumberExtension' => 'HM']
            ],
        ];
    }

    /**
     * @param array $street
     * @dataProvider parseAddressExceptionProvider
     */
    public function testParseAddressException(array $street)
    {
        $addressMock = $this->getMockBuilder(Address::class)->disableOriginalConstructor()->getMock();
        $addressMock->expects($this->once())->method('getStreet')->willReturn($street);

        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $this->entity->parseAddress($addressMock);
    }

    public function parseAddressExceptionProvider(): array
    {
        return [
            [
                ['spaarne']
            ],
            [
                [' Diemerkade str. ']
            ],
            [
                [' Diemerkade  ', ' str. ']
            ],
            [
                ['Oberländer']
            ],
            [
                ['Oberländer', '', ' Ufer ']
            ],
            [
                ['Сумская ул.']
            ],
        ];
    }

    /**
     * @param $configValue
     * @param $orderCurrency
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NotFoundException
     * @throws \ReflectionException
     * @dataProvider orderInsuredValueDataProvider
     */
    public function testOrderInsuredValue($configValue, $orderCurrency)
    {
        $orderId = 1;
        $orderStoreId = 2;

        $addressMock = $this->createMock(Address::class);
        $addressMock->method('getStreet')->willReturn(['Test str. 189']);

        $orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $orderMock->method('getEntityId')->willReturn($orderId);
        $orderMock->method('getStoreId')->willReturn($orderStoreId);
        $orderMock->method('getShippingAddress')->willReturn($addressMock);
        $orderMock->method('getBillingAddress')->willReturn($addressMock);
        $orderMock->method('getOrderCurrencyCode')->willReturn($orderCurrency);
        $orderMock->method('getItems')->willReturn([]);

        /** @var OrderReferenceInterface|MockObject $referenceMock */
        $referenceMock = $this->createMock(OrderReferenceInterface::class);

        $this->orderReferenceRepositoryMock->method('getByOrderId')
            ->with($orderId)
            ->willReturn($referenceMock);

        $shippingInfoMock = $this->createMock(ShippingInfo::class);
        $this->extInfoHandlerMock->method('getInfoFromOrderReference')->willReturn($shippingInfoMock);

        $this->configMock->method('getInsuranceValue')->with($orderStoreId)->willReturn($configValue);

        $result = $this->entity->getCreateOrderData($orderMock);

        $this->assertEquals($configValue, $result['insuranceValue']['value']);
        $this->assertEquals($orderCurrency, $result['insuranceValue']['currency']);
    }

    /**
     * @return array[]
     */
    public function orderInsuredValueDataProvider()
    {
        return [
            [0, 'EUR'],
            [0, 'USD'],
            [1.05, 'EUR'],
            [20.19, 'USD'],
            [201.36363, 'USD'],
        ];
    }
}
