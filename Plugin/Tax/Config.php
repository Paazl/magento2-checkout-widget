<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Plugin\Tax;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Model\Config as TaxConfigModel;
use Paazl\CheckoutWidget\Helper\Order as OrderHelper;
use Paazl\CheckoutWidget\Model\Config as PaazlConfig;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\CartRepositoryInterface;

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
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * Config constructor.
     *
     * @param OrderHelper $orderHelper
     * @param PaazlConfig $paazlConfig
     * @param ShippingMethodInterface $shippingMethod
     * @param Session $session
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        OrderHelper $orderHelper,
        PaazlConfig $paazlConfig,
        ShippingMethodInterface $shippingMethod,
        Session $session,
        CartRepositoryInterface $cartRepository
    ) {
        $this->orderHelper = $orderHelper;
        $this->paazlConfig = $paazlConfig;
        $this->shippingMethod = $shippingMethod;
        $this->session = $session;
        $this->cartRepository = $cartRepository;
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
        try {
            $cartId = $this->session->getQuoteId();
            //we can't use Magento\Checkout\Model\Session::getQuote() because of infinity loop on place order
            $quote = $this->cartRepository->getActive($cartId);
            $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
            if ($shippingMethod
                && $this->orderHelper->isPaazlShippingMethod($shippingMethod)
                && $this->paazlConfig->isPriceIncludesTax($store)
            ) {
                return true;
            }
        } catch (NoSuchEntityException $e) {
            return $result;
        }

        return $result;
    }
}
