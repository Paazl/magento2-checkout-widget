<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Plugin\Carrier;

use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\Data\ShippingMethodExtensionFactory;
use Paazl\CheckoutWidget\Model\Config;

/**
 * Class Infotext
 *
 * @package Paazl\CheckoutWidget\Plugin\Carrier
 */
class Infotext
{

    /**
     * @var ShippingMethodExtensionFactory
     */
    private $extensionFactory;

    /**
     * @var Config
     */
    private $scopeConfig;

    /**
     * Infotext constructor.
     *
     * @param ShippingMethodExtensionFactory $extensionFactory
     * @param Config $scopeConfig
     */
    public function __construct(
        ShippingMethodExtensionFactory $extensionFactory,
        Config $scopeConfig
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Add delivery date information to the carrier data object
     *
     * @param ShippingMethodConverter $subject
     * @param ShippingMethodInterface $result
     *
     * @return ShippingMethodInterface
     */
    public function afterModelToDataObject(ShippingMethodConverter $subject, ShippingMethodInterface $result)
    {
        $carrierCode = $result->getCarrierCode();
        if ($carrierCode == 'paazlshipping') {
            $infotext = $this->scopeConfig->getValue(
                'carriers/' . $carrierCode . '/infotext'
            );
            $extensibleAttribute = ($result->getExtensionAttributes())
                ? $result->getExtensionAttributes()
                : $this->extensionFactory->create();

            $extensibleAttribute->setInfotext($infotext);
            $result->setExtensionAttributes($extensibleAttribute);
        }

        return $result;
    }
}
