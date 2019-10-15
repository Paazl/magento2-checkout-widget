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
        $shippingMethod = $order->getShippingMethod();

        return $shippingMethod == Paazlshipping::CODE . '_' . Paazlshipping::CODE;
    }
}
