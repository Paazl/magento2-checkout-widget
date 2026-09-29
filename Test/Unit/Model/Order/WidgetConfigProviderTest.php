<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Test\Unit\Model\Order;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Item;
use Paazl\CheckoutWidget\Helper\General as GeneralHelper;
use Paazl\CheckoutWidget\Model\Checkout\LanguageProvider;
use Paazl\CheckoutWidget\Model\Config;
use Paazl\CheckoutWidget\Model\Handler\Item as ItemHandler;
use Paazl\CheckoutWidget\Model\Order\WidgetConfigProvider;
use Paazl\CheckoutWidget\Model\TokenRetriever;
use Paazl\CheckoutWidget\Test\Unit\UnitTestCase;

class WidgetConfigProviderTest extends UnitTestCase
{
    /**
     * @var WidgetConfigProvider
     */
    private $entity;

    protected function setUpWithoutVoid()
    {
        $itemHandler = $this->createStub(ItemHandler::class);
        $itemHandler->method('getPriceValue')->willReturn(10.0);

        $config = $this->createStub(Config::class);
        $config->method('getValue')->willReturn('de_DE');

        $this->entity = new WidgetConfigProvider(
            $config,
            $this->createStub(GeneralHelper::class),
            $itemHandler,
            $this->createStub(TokenRetriever::class),
            $this->createStub(LanguageProvider::class)
        );
    }

    public function testUsesOrderedQuantityOfOrderItems()
    {
        $address = $this->createStub(Address::class);
        $address->method('getCountryId')->willReturn('DE');
        $address->method('getPostcode')->willReturn('84347');

        $order = $this->createStub(Order::class);
        $order->method('getShippingAddress')->willReturn($address);
        $order->method('getAllVisibleItems')->willReturn([
            $this->createOrderItem(2.0, 0.5),
            $this->createOrderItem(1.0, 1.25)
        ]);

        $config = $this->entity->setOrder($order)->getConfig();

        $this->assertSame([2, 1], array_column($config['shipmentParameters']['goods'], 'quantity'));
        $this->assertSame(3, $config['shipmentParameters']['numberOfGoods']);
        $this->assertEqualsWithDelta(2.25, $config['shipmentParameters']['totalWeight'], 0.0001);
    }

    private function createOrderItem(float $qtyOrdered, float $weight): Item
    {
        $item = $this->createStub(Item::class);
        $item->method('getQtyOrdered')->willReturn($qtyOrdered);
        $item->method('getWeight')->willReturn($weight);

        return $item;
    }
}
