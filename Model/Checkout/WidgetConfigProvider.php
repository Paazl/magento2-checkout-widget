<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Checkout;

use Magento\Checkout\Helper\Data;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\OrderFactory;
use Paazl\CheckoutWidget\Helper\General as GeneralHelper;
use Paazl\CheckoutWidget\Model\Api\PaazlApiFactory;
use Paazl\CheckoutWidget\Model\Api\UrlProvider;
use Paazl\CheckoutWidget\Model\Config;
use Paazl\CheckoutWidget\Model\TokenRetriever;

/**
 * Class WidgetConfigProvider
 *
 * @package Paazl\CheckoutWidget\Model\Checkout
 */
class WidgetConfigProvider implements ConfigProviderInterface
{

    /**
     * @var Config
     */
    private $scopeConfig;

    /**
     * @var Data
     */
    private $checkoutHelper;

    /**
     * @var OrderFactory
     */
    private $order;

    /**
     * @var PaazlApiFactory
     */
    private $paazlApi;

    /**
     * @var GeneralHelper
     */
    private $generalHelper;

    /**
     * @var TokenRetriever
     */
    private $tokenRetriever;

    /**
     * @var UrlProvider
     */
    private $urlProvider;

    /**
     * Widget constructor.
     *
     * @param Config          $scopeConfig
     * @param Data            $checkoutHelper
     * @param OrderFactory    $order
     * @param PaazlApiFactory $paazlApi
     * @param GeneralHelper   $generalHelper
     * @param TokenRetriever  $tokenRetriever
     * @param UrlProvider     $urlProvider
     */
    public function __construct(
        Config $scopeConfig,
        Data $checkoutHelper,
        OrderFactory $order,
        PaazlApiFactory $paazlApi,
        GeneralHelper $generalHelper,
        TokenRetriever $tokenRetriever,
        UrlProvider $urlProvider
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->checkoutHelper = $checkoutHelper;
        $this->order = $order;
        $this->paazlApi = $paazlApi;
        $this->generalHelper = $generalHelper;
        $this->tokenRetriever = $tokenRetriever;
        $this->urlProvider = $urlProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        $countryId = $this->getDefaultCountry();
        $postcode = $this->getDefaultPostcode();

        $shippingAddress = $this->getQuote()->getShippingAddress();
        if ($shippingAddress->getCountryId() &&
            $shippingAddress->getPostcode()) {
            $countryId = $shippingAddress->getCountryId();
            $postcode = $shippingAddress->getPostcode();
        }

        $goods = [];
        foreach ($this->getQuote()->getAllVisibleItems() as $item) {
            $goods[] = [
                "quantity" => (int)$item->getQty(),
                "weight"   => doubleval($item->getWeight()),
                "price"    => $this->formatPrice($item->getPrice())
            ];
        }

        $config = [
            "mountElementId"             => "widget_paazlshipping_paazlshipping",
            "apiKey"                     => $this->getApiKey(),
            "token"                      => $this->getApiToken(),
            "loadPaazlBasedData"         => true,
            "loadCarrierBasedData"       => true,
            "availableTabs"              => $this->getAvailableTabs(),
            "defaultTab"                 => $this->getDefaultTab(),
            "style"                      => $this->getWidgetTheme(),
            "nominatedDateEnabled"       => $this->getNominatedDateEnabled(),
            "consigneeCountryCode"       => $countryId,
            "consigneePostalCode"        => $postcode,
            "numberOfProcessingDays"     => 0,
            "deliveryDateOptions"        => [
                "startDate"    => date("Y-m-d"),
                "numberOfDays" => 10
            ],
            "language"                   => "eng",
            "currency"                   => "EUR",
            "deliveryOptionDateFormat"   => "ddd DD MMM",
            "deliveryEstimateDateFormat" => "dddd DD MMMM",
            "pickupOptionDateFormat"     => "ddd DD MMM",
            "pickupEstimateDateFormat"   => "dddd DD MMMM",
            "sortingModel"               => [
                "orderBy"   => "PRICE",
                "sortOrder" => "ASC"
            ],
            "shipmentParameters"         => [
                "totalWeight"   => $this->getTotalWeight(),
                "totalPrice"    => $this->formatPrice($this->getQuote()->getSubtotal()),
                "numberOfGoods" => $this->getProductsCount(),
                "goods"         => $goods
            ],
            "shippingOptionsLimit"       => $this->getShippingOptionsLimit(),
            "pickupLocationsPageLimit"   => $this->getPickupLocationsPageLimit(),
            "pickupLocationsLimit"       => $this->getPickupLocationsLimit(),
            "initialPickupLocations"     => $this->getInitialPickupLocations()
        ];

        $this->generalHelper->addTolog('request', $config);

        return $config;
    }

    /**
     * @return Quote
     */
    public function getQuote()
    {
        return $this->checkoutHelper->getQuote();
    }

    /**
     * @return mixed
     */
    public function getDefaultCountry()
    {
        return $this->scopeConfig->getDefaultCountry();
    }

    /**
     * @return mixed
     */
    public function getDefaultPostcode()
    {
        return $this->scopeConfig->getDefaultPostcode();
    }

    /**
     * @param $price
     *
     * @return double
     */
    public function formatPrice($price)
    {
        return number_format($price, 2, ',', '');
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->scopeConfig->getApiKey();
    }

    /**
     * @return mixed
     */
    public function getApiToken()
    {
        try {
            return $this->tokenRetriever->retrieve($this->getQuote());
        } catch (LocalizedException $e) {
            $this->generalHelper->addTolog('exception', $e->getMessage());
        }

        return null;
    }

    /**
     * @return array
     */
    public function getAvailableTabs()
    {
        return $this->scopeConfig->getAvailableTabs();
    }

    /**
     * @return mixed
     */
    public function getDefaultTab()
    {
        return $this->scopeConfig->getDefaultTab();
    }

    /**
     * @return boolean
     */
    public function getWidgetTheme()
    {
        $widgetTheme = $this->scopeConfig->getWidgetTheme();
        return $widgetTheme == 'CUSTOM' ? 'DEFAULT' : $widgetTheme;
    }

    /**
     * @return string
     */
    public function getNominatedDateEnabled()
    {
        return $this->scopeConfig->getNominatedDateEnabled();
    }

    /**
     * @return float
     */
    public function getTotalWeight()
    {
        $weight = 0;
        $quote = $this->getQuote();
        foreach ($quote->getAllVisibleItems() as $_item) {
            $weight += $_item->getWeight();
        }
        return $weight;
    }

    /**
     * @return float
     */
    public function getProductsCount()
    {
        $count = 0;
        $quote = $this->getQuote();
        foreach ($quote->getAllVisibleItems() as $_item) {
            $count += $_item->getQty();
        }
        return $count;
    }

    /**
     * @return int
     */
    public function getShippingOptionsLimit()
    {
        return $this->scopeConfig->getShippingOptionsLimit();
    }

    /**
     * @return int
     */
    public function getPickupLocationsPageLimit()
    {
        return $this->scopeConfig->getPickupLocationsPageLimit();
    }

    /**
     * @return int
     */
    public function getPickupLocationsLimit()
    {
        return $this->scopeConfig->getPickupLocationsLimit();
    }

    /**
     * @return int
     */
    public function getInitialPickupLocations()
    {
        return $this->scopeConfig->getInitialPickupLocations();
    }

    /**
     * @return mixed
     */
    public function getCustomCss()
    {
        return $this->scopeConfig->getCustomCss();
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isEnabled();
    }

    /**
     * @return string
     */
    public function getApiBaseUrl()
    {
        return $this->urlProvider->getBaseUrl();
    }
}
