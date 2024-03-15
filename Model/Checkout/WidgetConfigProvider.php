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
use Paazl\CheckoutWidget\Helper\General as GeneralHelper;
use Paazl\CheckoutWidget\Model\Config;
use Paazl\CheckoutWidget\Model\Handler\Item as ItemHandler;
use Paazl\CheckoutWidget\Model\System\Config\Source\CalculateVolume;
use Paazl\CheckoutWidget\Model\System\Config\Source\DimensionsMetric;
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
    public const DEFAULT_NUMBER_OF_PROCESSING_DAYS = 0;
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
     * @param GeneralHelper     $generalHelper
     * @param ItemHandler       $itemHandler
     * @param TokenRetriever    $tokenRetriever
     * @param LanguageProvider  $languageProvider
     * @param ProductRepository $productRepository
     */
    public function __construct(
        Config $scopeConfig,
        Data $checkoutHelper,
        GeneralHelper $generalHelper,
        ItemHandler $itemHandler,
        TokenRetriever $tokenRetriever,
        LanguageProvider $languageProvider,
        ProductRepository $productRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->checkoutHelper = $checkoutHelper;
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

        $goods = [];

        $widthAttribute = $this->scopeConfig->getProductAttributeWidth();
        $heightAttribute = $this->scopeConfig->getProductAttributeHeight();
        $lengthAttribute = $this->scopeConfig->getProductAttributeLength();
        $useDimensions = $widthAttribute || $lengthAttribute || $heightAttribute;
        foreach ($this->getQuote()->getAllItems() as $item) {
            if ($item->getProductType() == 'simple') {
                $product = $this->productRepository->getById($item->getProduct()->getId());
                $goodsItem = [
                    "quantity" => $item->getParentItem()
                        ? (int)($item->getParentItem()->getQty())
                        : (int)$item->getQty(),
                    "weight" => doubleval($item->getWeight()),
                    "price" => $this->itemHandler->getPriceValue($item)
                ];
                if ($useDimensions) {
                    switch ($this->scopeConfig->getDimensionsMetric()) {
                        case DimensionsMetric::METRIC_MM:
                            $k = 0.1;
                            break;
                        case DimensionsMetric::METRIC_CM:
                            $k = 1;
                            break;
                        case DimensionsMetric::METRIC_M:
                            $k = 100;
                            break;
                        default:
                            $k = 1;
                    }
                    $goodsItem["length"] =
                        (float)str_replace(',', '.', $product->getData($lengthAttribute) ?? 0) * $k;
                    $goodsItem["width"] =
                        (float)str_replace(',', '.', $product->getData($widthAttribute) ?? 0) * $k;
                    $goodsItem["height"] =
                        (float)str_replace(',', '.', $product->getData($heightAttribute) ?? 0) * $k;
                }
                if ($this->scopeConfig->addVolume()) {
                    $goodsItem["volume"] = $this->getProductVolume($product);
                }

                if ($deliveryMatrixCode = $this->getProductDeliveryMatrix($item)) {
                    $goodsItem["startMatrix"] = $deliveryMatrixCode;
                }
                $goods[] = $goodsItem;
            }
        }

        $numberOfProcessingDays = self::DEFAULT_NUMBER_OF_PROCESSING_DAYS;
        foreach ($this->getQuote()->getAllItems() as $item) {
            if ($item->getProductType() == 'simple') {
                $itemNumberOfProcessingDays = $this->getProductNumberOfProcessingDays($item);
                if (!$itemNumberOfProcessingDays && $item->getParentItem()) {
                    $itemNumberOfProcessingDays = $this->getProductNumberOfProcessingDays($item->getParentItem());
                }
                if ($itemNumberOfProcessingDays && ($itemNumberOfProcessingDays > $numberOfProcessingDays)) {
                    $numberOfProcessingDays = (int)$itemNumberOfProcessingDays;
                }
            }
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
                "totalWeight"   => (float)$this->getTotalWeight($goods),
                "totalPrice"    => (float)$this->getQuote()->getSubtotalWithDiscount() -
                    $this->getQuote()->getShippingAddress()->getShippingAmount(),
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

        if ($this->scopeConfig->addVolume()) {
            $config['shipmentParameters']['totalVolume'] = $this->getTotalVolume($goods);
        }

        switch ($this->scopeConfig->getTotalPrice()) {
            case "grand_total":
                $totalPriceValue = (float) $shippingAddress->getGrandTotal() -
                    $this->getQuote()->getShippingAddress()->getShippingAmount();
                break;
            case "subtotal_excl_discount":
                $totalPriceValue = (float) $shippingAddress->getSubtotalInclTax() -
                    $this->getQuote()->getShippingAddress()->getShippingAmount();
                break;
            case "subtotal_incl_discount":
            default: // default from config.xml = "subtotal_incl_discount"
                $totalPriceValue = (
                    (float) $shippingAddress->getSubtotalInclTax() +
                    (float) $shippingAddress->getDiscountAmount() -
                    $this->getQuote()->getShippingAddress()->getShippingAmount()
                );
                break;
        }

        $config['shipmentParameters']['totalPrice'] = $totalPriceValue;
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
            $this->quote = $this->checkoutHelper->getQuote();
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
    public function getTotalWeight($goods)
    {
        $weight = 0;
        $quote = $this->getQuote();
        foreach ($goods as $good) {
            if (isset($good['weight']) && isset($good['quantity'])) {
                $weight += ($good['weight'] * $good['quantity']);
            }
        }
        return $weight;
    }

    /**
     * @return float
     */
    public function getTotalVolume($goods): float
    {
        $volume = 0;
        $quote = $this->getQuote();
        foreach ($goods as $good) {
            if (isset($good['volume']) && isset($good['quantity'])) {
                $volume += ($good['volume'] * $good['quantity']);
            }
        }
        return (float)$volume;
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

    /**
     * @param $product
     * @return float
     */
    private function getProductVolume($product): float
    {
        switch ($this->scopeConfig->addVolume()) {
            case CalculateVolume::CALCULATE_VOLUME_USE_ATTRIBUTE:
                return $product->getData($this->scopeConfig->getProductAttributeVolume());
            case CalculateVolume::CALCULATE_VOLUME_CALCULATE:
                return $this->calculateVolumeByDimensions($product);
            case CalculateVolume::CALCULATE_VOLUME_CALCULATE_IF_MISSED:
                return $product->getData($this->scopeConfig->getProductAttributeVolume()) ?:
                    $this->calculateVolumeByDimensions($product);
        }
    }

    /**
     * Calculate volume in m^3 using dimensions in cm
     *
     * @param $product
     * @return float
     */
    private function calculateVolumeByDimensions($product): float
    {
        switch ($this->scopeConfig->getDimensionsMetric()) {
            case DimensionsMetric::METRIC_MM:
                $k = 0.000000001;
                break;
            case DimensionsMetric::METRIC_CM:
                $k = 0.000001;
                break;
            case DimensionsMetric::METRIC_M:
                $k = 1;
                break;
            default:
                $k = 0.000001;
        }
        $widthAttribute = $this->scopeConfig->getProductAttributeWidth();
        $heightAttribute = $this->scopeConfig->getProductAttributeHeight();
        $lengthAttribute = $this->scopeConfig->getProductAttributeLength();
        return (float)str_replace(',', '.', $product->getData($widthAttribute) ?? 0) *
            (float)str_replace(',', '.', $product->getData($heightAttribute) ?? 0) *
            (float)str_replace(',', '.', $product->getData($lengthAttribute) ?? 0) *
            $k;
    }
}
