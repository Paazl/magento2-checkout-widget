<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Order;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order as OrderModel;
use Paazl\CheckoutWidget\Helper\General as GeneralHelper;
use Paazl\CheckoutWidget\Model\Checkout\LanguageProvider;
use Paazl\CheckoutWidget\Model\Config;
use Paazl\CheckoutWidget\Model\Handler\Item as ItemHandler;
use Paazl\CheckoutWidget\Model\TokenRetriever;

/**
 * Class WidgetConfigProvider
 */
class WidgetConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Config
     */
    private $scopeConfig;

    /**
     * @var OrderModel
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
     * Widget constructor.
     *
     * @param Config            $scopeConfig
     * @param GeneralHelper     $generalHelper
     * @param ItemHandler       $itemHandler
     * @param TokenRetriever    $tokenRetriever
     * @param LanguageProvider  $languageProvider
     */
    public function __construct(
        Config $scopeConfig,
        GeneralHelper $generalHelper,
        ItemHandler $itemHandler,
        TokenRetriever $tokenRetriever,
        LanguageProvider $languageProvider
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->generalHelper = $generalHelper;
        $this->itemHandler = $itemHandler;
        $this->tokenRetriever = $tokenRetriever;
        $this->languageProvider = $languageProvider;
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
        $shippingAddress = $this->getOrder()->getShippingAddress();

        if ($shippingAddress->getCountryId() &&
            $shippingAddress->getPostcode()) {
            $countryId = $shippingAddress->getCountryId();
            $postcode = $shippingAddress->getPostcode();
        }
        $goods = [];

        foreach ($this->getOrder()->getAllVisibleItems() as $item) {
            $goods[] = [
                'quantity' => (int)$item->getQty(),
                'weight'   => doubleval($item->getWeight()),
                'price'    => $this->itemHandler->getPriceValue($item)
            ];
        }
        $config = [
            'token'                      => $this->getApiToken(),
            'consigneeCountryCode'       => $countryId,
            'consigneePostalCode'        => $postcode,
            'numberOfProcessingDays'     => 0,
            'deliveryDateOptions'        => [
                'startDate'     => date('Y-m-d'),
                'numberOfDays'  => 10
            ],
            'shipmentParameters'         => [
                'totalWeight'   => $this->getTotalWeight(),
                'totalPrice'    => $this->getOrder()->getSubtotal(),
                'numberOfGoods' => $this->getProductsCount(),
                'goods'         => $goods
            ],
            'currency'                   => $this->getOrder()->getOrderCurrency(),
            'sortingModel'               => [
                'orderBy'       => 'PRICE',
                'sortOrder'     => 'ASC'
            ]
        ];
        $this->generalHelper->addTolog('Request to change from admin', $config);

        return $config;
    }

    /**
     * Retrieves default country code
     *
     * @return mixed
     */
    public function getDefaultCountry()
    {
        return $this->scopeConfig->getDefaultCountry();
    }

    /**
     * Retrieves default postcode
     *
     * @return mixed
     */
    public function getDefaultPostcode()
    {
        return $this->scopeConfig->getDefaultPostcode();
    }

    /**
     * Retrieves price in price format
     *
     * @param double|float $price
     * @return string
     * @deprecated
     */
    public function formatPrice($price)
    {
        return number_format($price, 2, '.', '');
    }

    /**
     * Retrieves API token
     *
     * @return mixed
     */
    public function getApiToken()
    {
        try {
            return $this->tokenRetriever->retrieveByOrder($this->getOrder());
        } catch (LocalizedException $e) {
            $this->generalHelper->addTolog('exception', $e->getMessage());
        }

        return null;
    }

    /**
     * Retrieves total order weight
     *
     * @return float
     */
    public function getTotalWeight()
    {
        $weight = 0;
        $order = $this->getOrder();

        foreach ($order->getAllVisibleItems() as $_item) {
            $weight += $_item->getWeight();
        }

        return $weight;
    }

    /**
     * Retrieves total order count of products
     *
     * @return float
     */
    public function getProductsCount()
    {
        $count = 0;
        $order = $this->getOrder();

        foreach ($order->getAllVisibleItems() as $_item) {
            $count += $_item->getQty();
        }

        return $count;
    }

    /**
     * Sets an order
     *
     * @param OrderModel $order
     * @return $this
     */
    public function setOrder(OrderModel $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Retrieves an order
     *
     * @return OrderModel
     */
    public function getOrder()
    {
        return $this->order;
    }
}
