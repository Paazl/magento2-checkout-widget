<?php
/**
 * Copyright © Paazl. All rights reserved.
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
    public const PICKUP_CODE = 'pickup_code';
    public const NAME = 'name';
    public const COUNTRY = 'country';
    public const POSTCODE = 'postcode';
    public const CITY = 'city';
    public const STREET = 'street';

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
