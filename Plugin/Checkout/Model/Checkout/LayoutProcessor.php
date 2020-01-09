<?php
namespace  Paazl\CheckoutWidget\Plugin\Checkout\Model\Checkout;


/**
 * Class LayoutProcessor
 * @package Paazl\CheckoutWidget\Plugin\Checkout\Model\Checkout
 */
class LayoutProcessor
{
    const BLOCK_NAME = 'paazl_checkoutwidget';
    const COMPONENT = 'Paazl_CheckoutWidget/js/checkout/view/widget-config';
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $subject, array $jsLayout)
    {

        $shippingAdditional = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shippingAdditional'];

        if (isset($shippingAdditional['children'])) {
            $shippingAdditional['children'][self::BLOCK_NAME] =
                [
                    'component' => self::COMPONENT
                ];
        }
        else {
            $shippingAdditional =
                [
                    'component' => 'uiComponent',
                    'displayArea' => 'shippingAdditional',
                    'children' => [
                        self::BLOCK_NAME => [
                            'component' => self::COMPONENT
                        ]
                    ]
                ];
        }

        return $jsLayout;
    }
}
