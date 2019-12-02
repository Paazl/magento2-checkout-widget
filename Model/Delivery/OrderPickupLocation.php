<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Delivery;

use Magento\Framework\Model\AbstractModel;
use Paazl\CheckoutWidget\Api\Data\Delivery\OrderPickupLocationInterface;

/**
 * Paazl Order Pickup Location Entity
 */
class OrderPickupLocation extends AbstractModel implements OrderPickupLocationInterface
{
    /**
     * @return string
     */
    public function getPickupCode()
    {
        return $this->getData(OrderPickupLocationInterface::PICKUP_CODE);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getData(OrderPickupLocationInterface::NAME);
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->getData(OrderPickupLocationInterface::COUNTRY);
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->getData(OrderPickupLocationInterface::POSTCODE);
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->getData(OrderPickupLocationInterface::CITY);
    }

    /**
     * @return string[]
     */
    public function getStreet()
    {
        return $this->getData(OrderPickupLocationInterface::STREET);
    }
}
