<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model;

use Magento\Framework\DataObject;

/**
 * Transport object for information selected with widget
 *
 * @package Paazl\CheckoutWidget\Model
 */
class ShippingInfo extends DataObject
{

    /**#@+
     * @var string
     */
    public const TYPE = 'type';
    public const IDENTIFIER = 'identifier';
    public const PICKUP_CODE = 'pickup_code';
    public const PICKUP_ACCOUNT_NUMBER = 'pickup_account_number';
    public const PICKUP_DATE = 'pickup_date';
    public const PICKUP_NAME = 'pickup_name';
    public const PICKUP_ADDRESS = 'pickup_address';
    public const PRICE = 'price';
    public const TITLE = 'title';
    public const OPTION_TITLE = 'option_title';
    public const ADDRESS = 'address';
    public const PREFERRED_DELIVERY_DATE = 'preferred_delivery_date';
    public const CARRIER_PICKUP_DATE = 'carrier_pickup_date';
    /**#@- */

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setType($value)
    {
        return $this->setData(self::TYPE, $value);
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setIdenfifier($value)
    {
        return $this->setData(self::IDENTIFIER, $value);
    }

    /**
     * @return string|null
     */
    public function getIdenfifier()
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setLocationCode($value)
    {
        return $this->setData(self::PICKUP_CODE, $value);
    }

    /**
     * @return string|null
     */
    public function getLocationCode()
    {
        return $this->getData(self::PICKUP_CODE);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setLocationAccountNumber($value)
    {
        return $this->setData(self::PICKUP_ACCOUNT_NUMBER, $value);
    }

    /**
     * @return string|null
     */
    public function getLocationAccountNumber()
    {
        return $this->getData(self::PICKUP_ACCOUNT_NUMBER);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setPickupDate($value)
    {
        return $this->setData(self::PICKUP_DATE, $value);
    }

    /**
     * @return string|null
     */
    public function getPickupDate()
    {
        return $this->getData(self::PICKUP_DATE);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setLocationName($value)
    {
        return $this->setData(self::PICKUP_NAME, $value);
    }

    /**
     * @return string|null
     */
    public function getLocationName()
    {
        return $this->getData(self::PICKUP_NAME);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setPickupAddress($value)
    {
        return $this->setData(self::PICKUP_ADDRESS, $value);
    }

    /**
     * @return array|null
     */
    public function getPickupAddress()
    {
        return $this->getData(self::PICKUP_ADDRESS);
    }

    /**
     * @param float $value
     * @return $this
     */
    public function setPrice($value)
    {
        return $this->setData(self::PRICE, $value);
    }

    /**
     * @return float|null
     */
    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setTitle($value)
    {
        return $this->setData(self::TITLE, $value);
    }

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setOptionTitle($value)
    {
        return $this->setData(self::OPTION_TITLE, $value);
    }

    /**
     * @return string|null
     */
    public function getOptionTitle()
    {
        return $this->getData(self::OPTION_TITLE);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setAddress($value)
    {
        return $this->setData(self::ADDRESS, $value);
    }

    /**
     * @return string|null
     */
    public function getAddress()
    {
        return $this->getData(self::ADDRESS);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setPreferredDeliveryDate($value)
    {
        return $this->setData(self::PREFERRED_DELIVERY_DATE, $value);
    }

    /**
     * @return string|null
     */
    public function getPreferredDeliveryDate()
    {
        return $this->getData(self::PREFERRED_DELIVERY_DATE);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setCarrierPickupDate($value)
    {
        return $this->setData(self::CARRIER_PICKUP_DATE, $value);
    }

    /**
     * @return string|null
     */
    public function getCarrierPickupDate()
    {
        return $this->getData(self::CARRIER_PICKUP_DATE);
    }
}
