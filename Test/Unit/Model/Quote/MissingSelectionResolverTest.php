<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Test\Unit\Model\Quote;

use Magento\Quote\Model\Quote;
use Paazl\CheckoutWidget\Helper\General;
use Paazl\CheckoutWidget\Model\Api\ApiException;
use Paazl\CheckoutWidget\Model\Api\Converter\ShippingOptions;
use Paazl\CheckoutWidget\Model\Api\PaazlApi;
use Paazl\CheckoutWidget\Model\Api\PaazlApiFactory;
use Paazl\CheckoutWidget\Model\Checkout\WidgetConfigProvider;
use Paazl\CheckoutWidget\Model\Config;
use Paazl\CheckoutWidget\Model\Quote\MissingSelectionResolver;
use Paazl\CheckoutWidget\Test\Unit\UnitTestCase;
use PHPUnit\Framework\MockObject\Stub;

class MissingSelectionResolverTest extends UnitTestCase
{
    /**
     * @var Config|Stub
     */
    private $config;

    /**
     * @var PaazlApi|Stub
     */
    private $api;

    /**
     * @var PaazlApiFactory|Stub
     */
    private $apiFactory;

    /**
     * @var WidgetConfigProvider|Stub
     */
    private $widgetConfigProvider;

    /**
     * @var ShippingOptions|Stub
     */
    private $converter;

    /**
     * @var Quote|Stub
     */
    private $quote;

    /**
     * @var MissingSelectionResolver
     */
    private $entity;

    protected function setUpWithoutVoid()
    {
        $this->config = $this->createStub(Config::class);
        $this->api = $this->createStub(PaazlApi::class);

        $this->apiFactory = $this->createStub(PaazlApiFactory::class);
        $this->apiFactory->method('create')->willReturn($this->api);

        $this->widgetConfigProvider = $this->createStub(WidgetConfigProvider::class);
        $this->widgetConfigProvider->method('setQuote')->willReturnSelf();
        $this->widgetConfigProvider->method('getConfig')->willReturn([]);

        $this->converter = $this->createStub(ShippingOptions::class);
        $this->quote = $this->createStub(Quote::class);

        $this->entity = $this->createResolver($this->apiFactory);
    }

    private function createResolver(PaazlApiFactory $apiFactory): MissingSelectionResolver
    {
        return new MissingSelectionResolver(
            $this->config,
            $apiFactory,
            $this->widgetConfigProvider,
            $this->converter,
            $this->createStub(General::class)
        );
    }

    public function testNotAllowedWhenConfigDisabledAndSkipsApiCall()
    {
        $this->config->method('getAllowWithoutShippingOption')->willReturn(false);
        $apiFactory = $this->createMock(PaazlApiFactory::class);
        $apiFactory->expects($this->never())->method('create');

        $this->assertFalse($this->createResolver($apiFactory)->isAllowedWithoutSelection($this->quote));
    }

    public function testNotAllowedWhenPaazlReturnsOptions()
    {
        $this->config->method('getAllowWithoutShippingOption')->willReturn(true);
        $this->api->method('getShippingOptions')->willReturn('{}');
        $this->converter->method('convert')->willReturn(['shippingOptions' => [['identifier' => 'DPD']]]);

        $this->assertFalse($this->entity->isAllowedWithoutSelection($this->quote));
    }

    public function testAllowedWhenPaazlReturnsNoOptions()
    {
        $this->config->method('getAllowWithoutShippingOption')->willReturn(true);
        $this->api->method('getShippingOptions')->willReturn('{}');
        $this->converter->method('convert')->willReturn(['shippingOptions' => []]);

        $this->assertTrue($this->entity->isAllowedWithoutSelection($this->quote));
    }

    public function testAllowedWhenPaazlCannotBeReached()
    {
        $this->config->method('getAllowWithoutShippingOption')->willReturn(true);
        $this->api->method('getShippingOptions')->willThrowException(new ApiException('timeout'));

        $this->assertTrue($this->entity->isAllowedWithoutSelection($this->quote));
    }
}
