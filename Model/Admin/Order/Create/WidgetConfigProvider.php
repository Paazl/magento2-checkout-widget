<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Admin\Order\Create;

use Magento\Backend\Model\Session\Quote as SessionQuote;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Sales\Model\OrderFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Paazl\CheckoutWidget\Helper\General as GeneralHelper;
use Paazl\CheckoutWidget\Model\Config;
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
     * @var SessionQuote
     */
    private $sessionQuote;

    /**
     * @var OrderFactory
     */
    private $order;

    /**
     * @var GeneralHelper
     */
    private $generalHelper;

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
     * Widget constructor.
     *
     * @param Config           $scopeConfig
     * @param SessionQuote     $sessionQuote
     * @param OrderFactory     $order
     * @param GeneralHelper    $generalHelper
     * @param TokenRetriever   $tokenRetriever
     * @param LanguageProvider $languageProvider
     * @param ProductRepository $productRepository
     */
    public function __construct(
        Config $scopeConfig,
        SessionQuote $sessionQuote,
        OrderFactory $order,
        GeneralHelper $generalHelper,
        TokenRetriever $tokenRetriever,
        LanguageProvider $languageProvider,
        ProductRepository $productRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->sessionQuote = $sessionQuote;
        $this->order = $order;
        $this->generalHelper = $generalHelper;
        $this->tokenRetriever = $tokenRetriever;
        $this->languageProvider = $languageProvider;
        $this->productRepository = $productRepository;
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

        $numberOfProcessingDays = self::DEFAULT_NUMBER_OF_PROCESSING_DAYS;
        $goods = [];
        foreach ($this->getQuote()->getAllVisibleItems() as $item) {
            $goodsItem = [
                "quantity" => (int)$item->getQty(),
                "weight"   => doubleval($item->getWeight()),
                "price"    => $this->formatPrice($item->getPrice())
            ];

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

        $config = array_merge($config, $this->languageProvider->getConfig());

        $this->generalHelper->addTolog('config', $config);

        return $config;
    }

    /**
     * @return Quote
     */
    public function getQuote()
    {
        return $this->sessionQuote->getQuote();
    }

    /**
     * @return mixed
     */
    public function getDefaultCountry()
    {
        return $this->scopeConfig->getDefaultCountry($this->getQuote()->getStoreId());
    }

    /**
     * @return mixed
     */
    public function getDefaultPostcode()
    {
        return $this->scopeConfig->getDefaultPostcode($this->getQuote()->getStoreId());
    }

    /**
     * @param double $price
     *
     * @return string
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
        return $this->scopeConfig->getAvailableTabs($this->getQuote()->getStoreId());
    }

    /**
     * @return mixed
     */
    public function getDefaultTab()
    {
        return $this->scopeConfig->getDefaultTab($this->getQuote()->getStoreId());
    }

    /**
     * @return string
     */
    public function getWidgetTheme()
    {
        $widgetTheme = $this->scopeConfig->getWidgetTheme($this->getQuote()->getStoreId());
        return $widgetTheme == 'CUSTOM' ? 'DEFAULT' : $widgetTheme;
    }

    /**
     * @return bool
     */
    public function getNominatedDateEnabled()
    {
        return $this->scopeConfig->getNominatedDateEnabled($this->getQuote()->getStoreId());
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
        $product = $this->productRepository->get($item->getSku());

        $attribute = $this->scopeConfig
            ->getProductAttributeNumberOfProcessingDays($this->getQuote()->getStoreId());
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
        return $this->scopeConfig->getShippingOptionsLimit($this->getQuote()->getStoreId());
    }

    /**
     * @return int
     */
    public function getPickupLocationsPageLimit()
    {
        return $this->scopeConfig->getPickupLocationsPageLimit($this->getQuote()->getStoreId());
    }

    /**
     * @return int
     */
    public function getPickupLocationsLimit()
    {
        return $this->scopeConfig->getPickupLocationsLimit($this->getQuote()->getStoreId());
    }

    /**
     * @return int
     */
    public function getInitialPickupLocations()
    {
        return $this->scopeConfig->getInitialPickupLocations($this->getQuote()->getStoreId());
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isEnabled($this->getQuote()->getStoreId());
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
        $product = $this->productRepository->get($item->getSku());

        $attribute = $this->scopeConfig
            ->getProductAttributeDeliveryMatrix($this->getQuote()->getStoreId());
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
}
