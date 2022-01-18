<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Plugin\Multishipping\Block\Checkout;

use Magento\Multishipping\Block\Checkout\Shipping as Subject;
use Paazl\CheckoutWidget\Model\Carrier\Paazlshipping;

/**
 * Class Address
 */
class Address
{
    /**
     * @param Subject $subject
     * @param $groups
     *
     * @return mixed
     */
    public function afterGetShippingRates(Subject $subject, $groups)
    {
        foreach ($groups as $code => $rates) {
            if ($code == Paazlshipping::CODE) {
                unset($groups[$code]);
            }
        }
        return $groups;
    }
}
