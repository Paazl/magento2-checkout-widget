<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Plugin\Shipping;

use Magento\Quote\Model\Quote\Address\RateCollectorInterface;
use Paazl\CheckoutWidget\Model\Carrier\Paazlshipping;

class RateCollectorPlugin
{
    /**
     * @var \Paazl\CheckoutWidget\Model\Config
     */
    private $config;

    /**
     * RateCollectorPlugin constructor.
     * @param \Paazl\CheckoutWidget\Model\Config $config
     */
    public function __construct(
        \Paazl\CheckoutWidget\Model\Config $config
    ) {
        $this->config = $config;
    }

    public function afterCollectRates(
        RateCollectorInterface $subject,
        RateCollectorInterface $result
    ) {

        /** @var \Magento\Shipping\Model\Rate\Result $ratesResult */
        $ratesResult = $result->getResult();

        /*
         * If Paazl is active
         *      and HideOtherCarriers exists - removing non-Paazl rates
         */
        $hideOthers = $this->config->isCarrierActive() && $this->config->isHideOtherShippingMethods();
        if ($hideOthers && !empty($ratesResult->getRatesByCarrier(Paazlshipping::CODE))) {
            // Removing other rates
            $rates = $ratesResult->getAllRates();
            $errorExists = $ratesResult->getError();
            $ratesResult->reset();

            /** @var \Magento\Quote\Model\Quote\Address\RateResult\AbstractResult $rate */
            foreach ($rates as $rateKey => $rate) {
                if ($rate->getCarrier() !== Paazlshipping::CODE) {
                    unset($rates[$rateKey]);
                }
            }

            $ratesResult->setError($errorExists);
            foreach ($rates as $rate) {
                $ratesResult->append($rate);
            }
        }

        return $result;
    }
}
