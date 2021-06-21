<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Plugin\Quote;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote as QuoteModel;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteRepository;
use Paazl\CheckoutWidget\Model\Config;

/**
 * Class AfterAddProduct
 * Plugin for quote model
 */
class AfterAddProduct
{

    const ORIGIN = 'shipping/origin/country_id';
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;
    /**
     * @var Config
     */
    private $config;

    /**
     * Quote constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param QuoteRepository $quoteRepository
     * @param Config $config
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        QuoteRepository $quoteRepository,
        Config $config
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->quoteRepository = $quoteRepository;
        $this->config = $config;
    }

    /**
     * Fire event after item was added to the cart (only after post request)
     *
     * @param QuoteModel $subject
     * @param Item $result
     * @param Product $product
     *
     * @return Item
     * @throws Exception
     */
    public function afterAddProduct(
        QuoteModel $subject,
        $result,
        Product $product
    ) {
        $shippingAddress = $subject->getShippingAddress();
        if (!$shippingAddress->getCountryId() && $this->config->isEnabled()) {
            $origin = $this->scopeConfig->getValue(self::ORIGIN);
            $shippingAddress = $subject->getShippingAddress();
            $billingAddress = $subject->getBillingAddress();
            $billingAddress->setCountryId($origin);
            $shippingAddress->setCountryId($origin);
            $subject->setShippingAddress($shippingAddress);
            $subject->setBillingAddress($billingAddress);
            $this->quoteRepository->save($subject);
        }
        return $result;
    }
}
