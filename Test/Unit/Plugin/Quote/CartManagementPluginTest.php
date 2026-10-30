<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Test\Unit\Plugin\Quote;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Paazl\CheckoutWidget\Helper\Order;
use Paazl\CheckoutWidget\Model\ExtInfoHandler;
use Paazl\CheckoutWidget\Model\ShippingInfo;
use Paazl\CheckoutWidget\Plugin\Quote\CartManagementPlugin;
use Paazl\CheckoutWidget\Test\Unit\UnitTestCase;
use PHPUnit\Framework\MockObject\Stub;

class CartManagementPluginTest extends UnitTestCase
{
    private const CART_ID = 12;

    /**
     * @var CartManagementPlugin
     */
    private $entity;

    /**
     * @var ExtInfoHandler|Stub
     */
    private $infoHandler;

    /**
     * @var CartManagementInterface|Stub
     */
    private $subject;

    protected function setUpWithoutVoid()
    {
        $address = $this->createStub(Address::class);
        $address->method('getShippingMethod')->willReturn('paazlshipping_paazlshipping');

        $quote = $this->createStub(Quote::class);
        $quote->method('getIsVirtual')->willReturn(false);
        $quote->method('getShippingAddress')->willReturn($address);

        $quoteRepository = $this->createStub(CartRepositoryInterface::class);
        $quoteRepository->method('getActive')->willReturn($quote);

        $orderHelper = $this->createStub(Order::class);
        $orderHelper->method('isPaazlShippingMethod')->willReturn(true);

        $this->infoHandler = $this->createStub(ExtInfoHandler::class);
        $this->subject = $this->createStub(CartManagementInterface::class);

        $this->entity = new CartManagementPlugin($quoteRepository, $orderHelper, $this->infoHandler);
    }

    public function testAllowsOrderWithSelectedShippingOption()
    {
        $this->infoHandler->method('getInfoFromQuote')->willReturn(new ShippingInfo(['identifier' => 'AVG']));

        $this->assertNull($this->entity->beforePlaceOrder($this->subject, self::CART_ID));
    }

    public function testBlocksOrderWithoutShippingInfo()
    {
        $this->infoHandler->method('getInfoFromQuote')->willReturn(null);

        $this->expectException(CouldNotSaveException::class);
        $this->entity->beforePlaceOrder($this->subject, self::CART_ID);
    }

    public function testBlocksOrderWithStoredEmptySelection()
    {
        $this->infoHandler->method('getInfoFromQuote')->willReturn(new ShippingInfo(['identifier' => null]));

        $this->expectException(CouldNotSaveException::class);
        $this->entity->beforePlaceOrder($this->subject, self::CART_ID);
    }
}
