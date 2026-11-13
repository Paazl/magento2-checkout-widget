<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Model\Quote;

use Magento\Quote\Model\Quote;
use Paazl\CheckoutWidget\Helper\General;
use Paazl\CheckoutWidget\Model\Api\Converter\ShippingOptions;
use Paazl\CheckoutWidget\Model\Api\PaazlApiFactory;
use Paazl\CheckoutWidget\Model\Checkout\WidgetConfigProvider;
use Paazl\CheckoutWidget\Model\Config;
use Throwable;

/**
 * Decides whether a quote may continue without a selected Paazl shipping option.
 *
 * That is only the case when "Allow orders when Paazl doesn't return a shipping option" is enabled
 * and Paazl indeed returns no shipping options for the quote. When options exist, the customer has
 * to select one.
 */
class MissingSelectionResolver
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var PaazlApiFactory
     */
    private $apiFactory;

    /**
     * @var WidgetConfigProvider
     */
    private $widgetConfigProvider;

    /**
     * @var ShippingOptions
     */
    private $shippingOptionsConverter;

    /**
     * @var General
     */
    private $generalHelper;

    /**
     * MissingSelectionResolver constructor.
     *
     * @param Config               $config
     * @param PaazlApiFactory      $apiFactory
     * @param WidgetConfigProvider $widgetConfigProvider
     * @param ShippingOptions      $shippingOptionsConverter
     * @param General              $generalHelper
     */
    public function __construct(
        Config $config,
        PaazlApiFactory $apiFactory,
        WidgetConfigProvider $widgetConfigProvider,
        ShippingOptions $shippingOptionsConverter,
        General $generalHelper
    ) {
        $this->config = $config;
        $this->apiFactory = $apiFactory;
        $this->widgetConfigProvider = $widgetConfigProvider;
        $this->shippingOptionsConverter = $shippingOptionsConverter;
        $this->generalHelper = $generalHelper;
    }

    /**
     * Whether the quote may be shipped with Paazl without a selected shipping option.
     *
     * @param Quote $quote
     * @return bool
     */
    public function isAllowedWithoutSelection(Quote $quote): bool
    {
        if (!$this->config->getAllowWithoutShippingOption($quote->getStoreId())) {
            return false;
        }

        try {
            $api = $this->apiFactory->create($quote->getStoreId());
            $config = $this->widgetConfigProvider->setQuote($quote)->getConfig();
            $shippingOptions = $this->shippingOptionsConverter->convert($api->getShippingOptions($config));
        } catch (Throwable $e) {
            // Paazl returned nothing usable, which is exactly the case this setting covers.
            $this->generalHelper->addTolog('exception', sprintf(
                'Could not retrieve shipping options for quote %s: %s',
                $quote->getId(),
                $e->getMessage()
            ));
            return true;
        }

        return empty($shippingOptions['shippingOptions']);
    }
}
