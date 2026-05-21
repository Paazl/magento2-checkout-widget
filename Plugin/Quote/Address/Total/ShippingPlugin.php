<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Plugin\Quote\Address\Total;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\Shipping as ShippingTotal;
use Paazl\CheckoutWidget\Model\Carrier\Paazlshipping;
use Paazl\CheckoutWidget\Model\Config;

class ShippingPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Paazl returns the rate already in the storefront display currency (e.g. a
     * DKK-configured matrix on a DKK storefront with an EUR base currency). Magento's
     * core shipping collector treats every carrier rate as a base-currency amount and
     * multiplies it by the base-to-quote rate, so the customer would be charged
     * rate * baseToQuoteRate. Pre-dividing in the carrier does not work either: core
     * rounds Rate\Method::setPrice() to 2 decimals before the multiplication, so a
     * sub-unit base value cannot round-trip exactly (6.00 -> 0.80 -> 5.97).
     *
     * Instead, re-split the already-collected total here: the displayed amount is the
     * exact Paazl rate, and base is derived from it once.
     *
     * @param ShippingTotal               $subject
     * @param ShippingTotal               $result
     * @param Quote                       $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total                       $total
     * @return ShippingTotal
     */
    public function afterCollect(
        ShippingTotal $subject,
        ShippingTotal $result,
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        if ($quote->getIsVirtual()) {
            return $result;
        }

        $method = (string)$shippingAssignment->getShipping()->getMethod();
        if (strpos($method, Paazlshipping::CODE) === false) {
            return $result;
        }

        if (!$this->config->isShippingPriceInDisplayCurrency($quote->getStoreId())) {
            return $result;
        }

        $address = $shippingAssignment->getShipping()->getAddress();

        $rate = null;
        foreach ($address->getAllShippingRates() as $shippingRate) {
            if ($shippingRate->getCode() === $method) {
                $rate = $shippingRate;
                break;
            }
        }
        if ($rate === null) {
            return $result;
        }

        // The carrier set the rate price to the exact Paazl amount in display currency.
        $displayAmount = (float)$rate->getPrice();

        $baseToQuoteRate = (float)$quote->getBaseToQuoteRate();
        $baseAmount = $baseToQuoteRate > 0
            ? round($displayAmount / $baseToQuoteRate, 2)
            : $displayAmount;

        $total->setShippingAmount($displayAmount);
        $total->setBaseShippingAmount($baseAmount);
        $total->setTotalAmount('shipping', $displayAmount);
        $total->setBaseTotalAmount('shipping', $baseAmount);

        $address->setShippingAmount($displayAmount);
        $address->setBaseShippingAmount($baseAmount);

        return $result;
    }
}
