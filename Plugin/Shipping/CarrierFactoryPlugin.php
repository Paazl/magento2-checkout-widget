<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Plugin\Shipping;

use Magento\Shipping\Model\CarrierFactory;
use Paazl\CheckoutWidget\Model\Carrier\Paazlshipping;
use Paazl\CheckoutWidget\Model\Config;

/**
 * Class CarrierFactoryPlugin
 *
 * @package Paazl\CheckoutWidget\Plugin\Shipping
 */
class CarrierFactoryPlugin
{

    /**
     * @var Config
     */
    private $config;

    /**
     * CarrierFactoryPlugin constructor.
     *
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param CarrierFactory $subject
     * @param \Closure       $proceed
     * @param                $carrierCode
     * @param null           $storeId
     *
     * @return bool|mixed
     */
    public function aroundCreateIfActive(
        CarrierFactory $subject,
        \Closure $proceed,
        $carrierCode,
        $storeId = null
    ) {
        $paazlActive = $this->config->isCarrierActive() && $this->config->isHideOtherShippingMethods();
        if ($paazlActive && ($carrierCode != Paazlshipping::CODE)) {
            // This is not Paazl, but Paazl is active - deactivating this method
            return false;
        }

        return $proceed($carrierCode, $storeId);
    }
}
