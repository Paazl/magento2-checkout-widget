<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Plugin\Tax;

use Magento\Tax\Model\Config as TaxConfigModel;
use Paazl\CheckoutWidget\Helper\Order as OrderHelper;
use Paazl\CheckoutWidget\Model\Config as PaazlConfig;
use Magento\Checkout\Model\Session;
use Paazl\CheckoutWidget\Model\ResourceModel\QuoteAddress\Resource as QuoteAddressResource;

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
     * Config constructor.
     *
     * @param OrderHelper $orderHelper
     * @param PaazlConfig $paazlConfig
     * @param Session $session
     * @param QuoteAddressResource $quoteAddressResource
     */
    public function __construct(
        OrderHelper $orderHelper,
        PaazlConfig $paazlConfig,
        Session $session,
        QuoteAddressResource $quoteAddressResource
    ) {
        $this->orderHelper = $orderHelper;
        $this->paazlConfig = $paazlConfig;
        $this->session = $session;
        $this->quoteAddressResource = $quoteAddressResource;
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
        $cartId = $this->session->getQuoteId();
        $shippingMethod = $this->quoteAddressResource->getShippingMethodByQuoteId((int)$cartId);
        if ($shippingMethod
            && $this->orderHelper->isPaazlShippingMethod($shippingMethod)
            && $this->paazlConfig->isPriceIncludesTax($store)
        ) {
            return true;
        }

        return $result;
    }
}
