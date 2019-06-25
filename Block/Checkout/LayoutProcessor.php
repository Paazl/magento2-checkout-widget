<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Paazl\CheckoutWidget\Model\Config;

/**
 * Class LayoutProcessor
 *
 * @package Paazl\CheckoutWidget\Block\Checkout
 */
class LayoutProcessor implements LayoutProcessorInterface
{

    /**
     * @var Config
     */
    private $config;

    /**
     * LayoutProcessor constructor.
     *
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     *
     * @return array
     */
    public function process($jsLayout)
    {
        $isCheckoutEnabled = $this->config->isEnabled();

        if (!$isCheckoutEnabled) {
            $shippingStep = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step'];
            // @codingStandardsIgnoreLine
            unset($shippingStep['children']['step-config']['children']['shipping-rates-validation']['children']['paazlshipping-rates-validation']);
        }

        return $jsLayout;
    }
}
