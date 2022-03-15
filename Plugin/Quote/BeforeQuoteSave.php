<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Plugin\Quote;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteRepository;
use Paazl\CheckoutWidget\Model\Config;

/**
 * Class BeforeQuoteSave
 * Plugin for quote repository
 */
class BeforeQuoteSave
{

    public const ORIGIN = 'shipping/origin/country_id';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var Config
     */
    private $config;

    /**
     * Quote constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $config
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $config
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
    }

    /**
     * @param QuoteRepository $subject
     * @param CartInterface $quote
     *
     * @return CartInterface[]
     */
    public function beforeSave(
        QuoteRepository $subject,
        CartInterface $quote
    ) {
        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress->getCountryId() && $this->config->isEnabled()) {
            $origin = $this->scopeConfig->getValue(self::ORIGIN);
            $shippingAddress = $quote->getShippingAddress();
            $billingAddress = $quote->getBillingAddress();
            $billingAddress->setCountryId($origin);
            $shippingAddress->setCountryId($origin);
            $quote->setShippingAddress($shippingAddress);
            $quote->setBillingAddress($billingAddress);
        }
        return [$quote];
    }
}
