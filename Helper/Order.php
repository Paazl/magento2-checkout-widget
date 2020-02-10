<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Helper;

use Magento\Sales\Api\Data\OrderInterface;
use Paazl\CheckoutWidget\Model\Carrier\Paazlshipping;

/**
 * Class Order
 *
 * @package Paazl\CheckoutWidget\Helper
 */
class Order
{
    /**
     * @param OrderInterface $order
     *
     * @return bool
     */
    public function isPaazlOrder(OrderInterface $order)
    {
        return $this->isPaazlShippingMethod($order->getShippingMethod());
    }

    /**
     * @param string $shippingMethod
     *
     * @return bool
     */
    public function isPaazlShippingMethod(string $shippingMethod)
    {
        $params = explode('_', $shippingMethod, 2);
        return !empty($params[0]) && ($params[0] === Paazlshipping::CODE);
    }
}
