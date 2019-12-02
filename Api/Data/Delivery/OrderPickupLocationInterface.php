<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Api\Data\Delivery;

/**
 * Paazl Order Pickup Location Interface
 *
 * @api
 */
interface OrderPickupLocationInterface
{
    const PICKUP_CODE = 'pickup_code';
    const NAME = 'name';
    const COUNTRY = 'country';
    const POSTCODE = 'postcode';
    const CITY = 'city';
    const STREET = 'street';

    /**
     * @return string
     */
    public function getPickupCode();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getCountry();

    /**
     * @return string
     */
    public function getPostcode();

    /**
     * @return string
     */
    public function getCity();

    /**
     * @return string[]
     */
    public function getStreet();
}
