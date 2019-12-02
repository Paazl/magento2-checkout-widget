<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Location;

use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderAddressInterfaceFactory;
use Magento\Sales\Model\Order\Address;
use Paazl\CheckoutWidget\Api\Data\Delivery\OrderPickupLocationInterface;

/**
 * Paazl Order Address Factory
 * Creates order address from different location entities
 */
class OrderAddressFactory
{
    /**
     * @var OrderAddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * OrderAddressFactory constructor.
     * @param OrderAddressInterfaceFactory $addressFactory
     * @param RegionFactory $regionFactory
     */
    public function __construct(OrderAddressInterfaceFactory $addressFactory, RegionFactory $regionFactory)
    {
        $this->addressFactory = $addressFactory;
        $this->regionFactory = $regionFactory;
    }

    /**
     * @param string[] $addressData
     * @return OrderAddressInterface
     */
    private function create(array $addressData)
    {
        try {
            $address = $this->addressFactory->create(['data' => $addressData]);
        } catch (LocalizedException $e) {
            $address = $this->addressFactory->create(['data' => $addressData]);
        }

        $address->setAddressType(Address::TYPE_SHIPPING);
        return $address;
    }

    /**
     * @param OrderPickupLocationInterface $location
     * @return OrderAddressInterface
     */
    public function createFromPickupLocation(OrderPickupLocationInterface $location)
    {
        $addressData = [
            'company'    => $location->getName(),
            'street'     => $location->getStreet(),
            'city'       => $location->getCity(),
            'postcode'   => $location->getPostcode(),
            'country_id' => $location->getCountry(),
        ];

        return $this->create($addressData);
    }
}
