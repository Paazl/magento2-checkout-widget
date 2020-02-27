<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Api\Builder;

use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;
use Paazl\CheckoutWidget\Model\Config;

/**
 * Class Reference
 *
 * @package Paazl\CheckoutWidget\Model\Api\Builder
 */
class Reference
{

    /**
     * @var Config
     */
    private $config;

    /**
     * Reference constructor.
     *
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param Quote $quote
     *
     * @return string
     */
    public function getQuoteReference(Quote $quote)
    {
        return $this->config->getReferencePrefix($quote->getStoreId()) . $quote->getId();
    }

    /**
     * @param OrderInterface $order
     *
     * @return string
     */
    public function getOrderReference(OrderInterface $order)
    {
        return $this->config->getReferencePrefix($order->getStoreId()) . $order->getIncrementId();
    }
}
