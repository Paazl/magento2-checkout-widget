<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Test\Unit\Plugin\Checkout;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Paazl\CheckoutWidget\Model\Api\Builder\Reference;
use Paazl\CheckoutWidget\Model\Api\PaazlApi;
use Paazl\CheckoutWidget\Model\Api\PaazlApiFactory;
use Paazl\CheckoutWidget\Model\Api\Processor\CheckoutInfoToQuote;
use Paazl\CheckoutWidget\Model\Carrier\Paazlshipping;
use Paazl\CheckoutWidget\Model\Config;
use Paazl\CheckoutWidget\Model\ExtInfoHandler;
use Paazl\CheckoutWidget\Model\Quote\MissingSelectionResolver;
use Paazl\CheckoutWidget\Model\Quote\Totals\AppendShippingMethods;
use Paazl\CheckoutWidget\Model\ShippingInfo;
use Paazl\CheckoutWidget\Plugin\Checkout\ShippingInformationManagementPlugin;
use Paazl\CheckoutWidget\Test\Unit\UnitTestCase;
use PHPUnit\Framework\MockObject\Stub;

class ShippingInformationManagementPluginTest extends UnitTestCase
{
    private const CART_ID = 12;

    /**
     * @var Config|Stub
     */
    private $config;

    /**
     * @var ExtInfoHandler|Stub
     */
    private $extInfoHandler;

    /**
     * @var MissingSelectionResolver|Stub
     */
    private $missingSelectionResolver;

    /**
     * @var ShippingInformationInterface|Stub
     */
    private $addressInformation;

    /**
     * @var ShippingInformationManagementPlugin
     */
    private $entity;

    protected function setUpWithoutVoid()
    {
        $shippingAddress = $this->createStub(Address::class);

        $quote = $this->createStub(Quote::class);
        $quote->method('getShippingAddress')->willReturn($shippingAddress);

        $quoteRepository = $this->createStub(CartRepositoryInterface::class);
        $quoteRepository->method('getActive')->willReturn($quote);

        $paazlApi = $this->createStub(PaazlApi::class);
        $paazlApi->method('fetchCheckoutData')->willReturn(['deliveryType' => 'DELIVERY']);
        $paazlApiFactory = $this->createStub(PaazlApiFactory::class);
        $paazlApiFactory->method('create')->willReturn($paazlApi);

        $this->addressInformation = $this->createStub(ShippingInformationInterface::class);
        $this->addressInformation->method('getShippingCarrierCode')->willReturn(Paazlshipping::CODE);

        $this->config = $this->createStub(Config::class);
        $this->extInfoHandler = $this->createStub(ExtInfoHandler::class);
        $this->missingSelectionResolver = $this->createStub(MissingSelectionResolver::class);

        $this->entity = new ShippingInformationManagementPlugin(
            $paazlApiFactory,
            $this->createStub(Reference::class),
            $quoteRepository,
            $this->createStub(CheckoutInfoToQuote::class),
            $this->createStub(ShipmentEstimationInterface::class),
            new ArrayManager(),
            $this->createStub(AppendShippingMethods::class),
            $this->config,
            $this->extInfoHandler,
            $this->missingSelectionResolver
        );
    }

    private function save()
    {
        return $this->entity->beforeSaveAddressInformation(
            $this->createStub(ShippingInformationManagement::class),
            self::CART_ID,
            $this->addressInformation
        );
    }

    public function testAcceptsSelectedDeliveryOption()
    {
        $this->extInfoHandler->method('getInfoFromQuote')->willReturn(new ShippingInfo(['identifier' => 'DPD']));

        $this->assertNull($this->save());
    }

    public function testRejectsMissingSelectionWhenOptionsAreAvailable()
    {
        $this->extInfoHandler->method('getInfoFromQuote')->willReturn(new ShippingInfo(['identifier' => null]));
        $this->missingSelectionResolver->method('isAllowedWithoutSelection')->willReturn(false);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Please select a delivery option.');
        $this->save();
    }

    public function testAcceptsMissingSelectionWhenAllowedWithoutShippingOption()
    {
        $this->extInfoHandler->method('getInfoFromQuote')->willReturn(new ShippingInfo(['identifier' => null]));
        $this->missingSelectionResolver->method('isAllowedWithoutSelection')->willReturn(true);

        $this->assertNull($this->save());
    }

    public function testSkipsCheckForOneStepCheckout()
    {
        $this->config->method('saveShippingInformationInstantly')->willReturn(true);
        $this->extInfoHandler->method('getInfoFromQuote')->willReturn(null);
        $this->missingSelectionResolver->method('isAllowedWithoutSelection')->willReturn(false);

        $this->assertNull($this->save());
    }
}
