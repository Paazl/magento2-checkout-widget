<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Checkout;

use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Helper\Data;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Sales\Model\OrderFactory;
use Paazl\CheckoutWidget\Helper\General as GeneralHelper;
use Paazl\CheckoutWidget\Model\Config;
use Paazl\CheckoutWidget\Model\Handler\Item as ItemHandler;
use Paazl\CheckoutWidget\Model\TokenRetriever;

/**
 * Class WidgetConfigProvider
 *
 * @package Paazl\CheckoutWidget\Model\Checkout
 */
class WidgetConfigProvider implements ConfigProviderInterface
{
    /**#@+
     * Constants
     */
    const DEFAULT_NUMBER_OF_PROCESSING_DAYS = 0;
    /**#@-*/

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
     * @var GeneralHelper
     */
    private $generalHelper;

    /**
     * @var ItemHandler
     */
    private $itemHandler;

    /**
     * @var TokenRetriever
     */
    private $tokenRetriever;

    /**
     * @var LanguageProvider
     */
    private $languageProvider;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * Widget constructor.
     *
     * @param Config            $scopeConfig
     * @param Data              $checkoutHelper
     * @param OrderFactory      $order
     * @param GeneralHelper     $generalHelper
     * @param ItemHandler       $itemHandler
     * @param TokenRetriever    $tokenRetriever
     * @param LanguageProvider  $languageProvider
     * @param ProductRepository $productRepository
     */
    public function __construct(
        Config $scopeConfig,
        Data $checkoutHelper,
        OrderFactory $order,
        GeneralHelper $generalHelper,
        ItemHandler $itemHandler,
        TokenRetriever $tokenRetriever,
        LanguageProvider $languageProvider,
        ProductRepository $productRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->checkoutHelper = $checkoutHelper;
        $this->order = $order;
        $this->generalHelper = $generalHelper;
        $this->itemHandler = $itemHandler;
        $this->tokenRetriever = $tokenRetriever;
        $this->languageProvider = $languageProvider;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritDoc}
     *
     * @throws LocalizedException
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

        $numberOfProcessingDays = self::DEFAULT_NUMBER_OF_PROCESSING_DAYS;
        $goods = [];
        $widthAttribute = $this->scopeConfig->getProductAttributeWidth();
        $heightAttribute = $this->scopeConfig->getProductAttributeHeight();
        $lengthAttribute = $this->scopeConfig->getProductAttributeLength();
        $useDimensions = $widthAttribute && $lengthAttribute && $heightAttribute;
        foreach ($this->getQuote()->getAllVisibleItems() as $item) {
            $goodsItem = [
                "quantity" => (int)$item->getQty(),
                "weight"   => doubleval($item->getWeight()),
                "price"    => $this->itemHandler->getPriceValue($item)
            ];
            if ($useDimensions) {
                $product = $this->productRepository->getById($item->getProduct()->getId());
                $goodsItem["length"] = (float) $product->getData($lengthAttribute);
                $goodsItem["width"] = (float) $product->getData($widthAttribute);
                $goodsItem["height"] = (float) $product->getData($heightAttribute);
            }

            if (($itemNumberOfProcessingDays = $this->getProductNumberOfProcessingDays($item))
                && $itemNumberOfProcessingDays > $numberOfProcessingDays) {
                $numberOfProcessingDays = (int)$itemNumberOfProcessingDays;
            }

            if ($deliveryMatrixCode = $this->getProductDeliveryMatrix($item)) {
                $goodsItem["startMatrix"] = $deliveryMatrixCode;
            }
            $goods[] = $goodsItem;
        }

        $config = [
            "mountElementId"             => "widget_paazlshipping_paazlshipping",
            "apiKey"                     => $this->getApiKey(),
            "token"                      => $this->getApiToken(),
            "loadPaazlBasedData"         => true,
            "loadCarrierBasedData"       => true,
            "availableTabs"              => $this->getAvailableTabs(),
            "headerTabType"              => $this->getWidgetSectionToggle(),
            "defaultTab"                 => $this->getDefaultTab(),
            "style"                      => $this->getWidgetTheme(),
            "nominatedDateEnabled"       => $this->getNominatedDateEnabled(),
            "consigneeCountryCode"       => $countryId,
            "consigneePostalCode"        => $postcode,
            "numberOfProcessingDays"     => $numberOfProcessingDays,
            "deliveryDateOptions"        => [
                "startDate"    => date("Y-m-d"),
                "numberOfDays" => 10
            ],
            "currency"                   => $this->getQuote()->getQuoteCurrencyCode(),
            "deliveryOptionDateFormat"   => "ddd DD MMM",
            "deliveryEstimateDateFormat" => "dddd DD MMMM",
            "pickupOptionDateFormat"     => "ddd DD MMM",
            "pickupEstimateDateFormat"   => "dddd DD MMMM",
            "sortingModel"               => [
                "orderBy"   => "PRICE",
                "sortOrder" => "ASC"
            ],
            "shipmentParameters"         => [
                "totalWeight"   => (float)$this->getTotalWeight(),
                "totalPrice"    => (float)$this->getQuote()->getSubtotalWithDiscount(),
                "numberOfGoods" => (int)$this->getProductsCount(),
                "goods"         => $goods
            ],
            "shippingOptionsLimit"       => $this->getShippingOptionsLimit(),
            "pickupLocationsPageLimit"   => $this->getPickupLocationsPageLimit(),
            "pickupLocationsLimit"       => $this->getPickupLocationsLimit(),
            "initialPickupLocations"     => $this->getInitialPickupLocations()
        ];

        if ($this->isFreeShippingEnabled() && $shippingAddress->getFreeShipping()) {
            $config['shipmentParameters']['startMatrix'] = $this->getFreeShippingMatrixLetter();
        }
        if ($this->scopeConfig->getTotalPrice() == 'grand_total') {
            $config['shipmentParameters']['totalPrice'] = (float)$this->getQuote()->getGrandTotal();
        } elseif ($this->scopeConfig->getTotalPrice() == 'subtotal_excl_discount') {
            $config['shipmentParameters']['totalPrice'] = (float)$this->getQuote()->getSubtotal();
        }

        $config = array_merge($config, $this->languageProvider->getConfig());

        $this->generalHelper->addTolog('request', $config);

        return $config;
    }

    /**
     * @return Quote
     */
    public function getQuote()
    {
        if (!$this->quote) {
            return $this->checkoutHelper->getQuote();
        }

        return $this->quote;
    }

    /**
     * @param Quote|null $quote
     * @return $this
     */
    public function setQuote(Quote $quote = null)
    {
        $this->quote = $quote;
        return $this;
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
     * @param double $price
     *
     * @return string
     * @deprecated
     */
    public function formatPrice($price)
    {
        return number_format($price, 2, '.', '');
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->scopeConfig->getApiKey($this->getQuote()->getStoreId());
    }

    /**
     * @return mixed
     */
    public function getApiToken()
    {
        try {
            return $this->tokenRetriever->retrieveByQuote($this->getQuote());
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
    public function getWidgetSectionToggle()
    {
        return $this->scopeConfig->getWidgetSectionToggle();
    }

    /**
     * @return mixed
     */
    public function getDefaultTab()
    {
        return $this->scopeConfig->getDefaultTab();
    }

    /**
     * @return string
     */
    public function getWidgetTheme()
    {
        $widgetTheme = $this->scopeConfig->getWidgetTheme();
        return $widgetTheme == 'CUSTOM' ? 'DEFAULT' : $widgetTheme;
    }

    /**
     * @return bool
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
     * Gets number of processing days from product
     *
     * @param AbstractItem $item
     * @return int|mixed|null
     * @throws NoSuchEntityException
     */
    public function getProductNumberOfProcessingDays(AbstractItem $item)
    {
        $product = $this->productRepository->getById($item->getProduct()->getId());

        $attribute = $this->scopeConfig
            ->getProductAttributeNumberOfProcessingDays();
        if ($attribute) {
            if (($numberOfProcessingDays = $product->getData($attribute)) !== null) {
                if (is_numeric($numberOfProcessingDays)
                    && $numberOfProcessingDays >= Config::MIN_NUMBER_OF_PROCESSING_DAYS
                    && $numberOfProcessingDays <= Config::MAX_NUMBER_OF_PROCESSING_DAYS
                ) {
                    return $numberOfProcessingDays;
                }
            }
        }

        return null;
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
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isEnabled();
    }

    /**
     * @return mixed
     */
    public function getFreeShippingMatrixLetter()
    {
        return $this->scopeConfig->getFreeShippingMatrixLetter($this->getQuote()->getStoreId());
    }

    /**
     * Gets delivery matrix from product
     *
     * @param AbstractItem $item
     * @return int|mixed|null
     * @throws NoSuchEntityException
     */
    public function getProductDeliveryMatrix(AbstractItem $item)
    {
        $product = $this->productRepository->getById($item->getProduct()->getId());

        $attribute = $this->scopeConfig
            ->getProductAttributeDeliveryMatrix();
        if ($attribute) {
            if (($deliveryMatrixCode = $product->getData($attribute)) !== null
                && $this->validateDeliveryMatrixCode($deliveryMatrixCode)
            ) {
                return $deliveryMatrixCode;
            }
        }

        return null;
    }

    /**
     * Validates matrix code according to Paazl instructions
     *
     * @param string $value
     * @return bool
     */
    protected function validateDeliveryMatrixCode(string $value)
    {
        preg_match('/^[A-Z]{1,2}$/', $value, $matches);

        return count($matches) === 1;
    }

    /**
     * Check if free shipping enabled in store config
     *
     * @return bool
     */
    protected function isFreeShippingEnabled()
    {
        return $this->scopeConfig->isFreeShippingEnabled($this->getQuote()->getStoreId());
    }
}
