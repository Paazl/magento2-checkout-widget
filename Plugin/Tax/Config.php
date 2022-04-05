<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Plugin\Tax;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Config as TaxConfigModel;
use Paazl\CheckoutWidget\Helper\Order as OrderHelper;
use Paazl\CheckoutWidget\Model\Config as PaazlConfig;
use Magento\Checkout\Model\Session;
use Paazl\CheckoutWidget\Model\ResourceModel\QuoteAddress\ResourceModel as QuoteAddressResource;

/**
 * Tax Config Plugin
 */
class Config
{
    /**
     * @var OrderHelper
     */
    private $orderHelper;

    /**
     * @var PaazlConfig
     */
    private $paazlConfig;
    /**
     * @var Session
     */
    private $session;

    /**
     * @var QuoteAddressResource
     */
    private $quoteAddressResource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Config constructor.
     *
     * @param OrderHelper $orderHelper
     * @param PaazlConfig $paazlConfig
     * @param Session $session
     * @param QuoteAddressResource $quoteAddressResource
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        OrderHelper $orderHelper,
        PaazlConfig $paazlConfig,
        Session $session,
        QuoteAddressResource $quoteAddressResource,
        StoreManagerInterface $storeManager
    ) {
        $this->orderHelper = $orderHelper;
        $this->paazlConfig = $paazlConfig;
        $this->session = $session;
        $this->quoteAddressResource = $quoteAddressResource;
        $this->storeManager = $storeManager;
    }

    /**
     * @param TaxConfigModel $subject
     * @param bool $result
     * @param null $store
     *
     * @return bool
     */
    public function afterShippingPriceIncludesTax(
        TaxConfigModel $subject,
        bool $result,
        $store = null
    ) {
        $cartId = $this->session->getData($this->getQuoteIdKey());
        $shippingMethod = $this->quoteAddressResource->getShippingMethodByQuoteId((int)$cartId);
        if ($shippingMethod
            && $this->orderHelper->isPaazlShippingMethod((string)$shippingMethod)
            && $this->paazlConfig->isPriceIncludesTax($store)
        ) {
            return true;
        }

        return $result;
    }

    /**
     * @return string
     */
    private function getQuoteIdKey()
    {
        return 'quote_id_' . $this->storeManager->getStore()->getWebsiteId();
    }
}
