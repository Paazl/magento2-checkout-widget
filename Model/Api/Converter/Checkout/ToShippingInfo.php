<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Api\Converter\Checkout;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\ArrayManager;
use Paazl\CheckoutWidget\Model\Api\Field\DeliveryType;
use Paazl\CheckoutWidget\Model\ShippingInfo;
use Paazl\CheckoutWidget\Model\ShippingInfoFactory;

/**
 * Class ToShippingInfo
 * Converts response from Checkout API to internal ShippingInfo object
 *
 * @package Paazl\CheckoutWidget\Model\Api\Converter\Checkout
 */
class ToShippingInfo
{

    /**
     * @var ShippingInfoFactory
     */
    private $shippingInfoFactory;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * ToShippingInfo constructor.
     *
     * @param ShippingInfoFactory $shippingInfoFactory
     * @param Json                $json
     * @param ArrayManager        $arrayManager
     */
    public function __construct(
        ShippingInfoFactory $shippingInfoFactory,
        Json $json,
        ArrayManager $arrayManager
    ) {
        $this->shippingInfoFactory = $shippingInfoFactory;
        $this->json = $json;
        $this->arrayManager = $arrayManager;
    }

    /**
     * @param string|array $response
     *
     * @return ShippingInfo
     * @throws \InvalidArgumentException
     */
    public function convert($response)
    {
        $info = $this->shippingInfoFactory->create();

        $result = $response;
        if (!is_array($result) && !empty($result)) {
            $result = $this->json->unserialize($response);
        }

        // A response without a selection converts to an empty selection (no identifier), which
        // clears any earlier one; CartManagementPlugin blocks placing an order without an identifier.
        if (!is_array($result)) {
            $result = [];
        }

        $info->setType($this->arrayManager->get('deliveryType', $result));
        $info->setIdenfifier($this->arrayManager->get('shippingOption/identifier', $result));
        $info->setPrice(floatval($this->arrayManager->get('shippingOption/rate', $result)));
        $info->setOptionTitle($this->arrayManager->get('shippingOption/name', $result));
        $info->setCarrierDescription($this->arrayManager->get('shippingOption/carrier/description', $result));

        // A nominated date is only returned on the root level; without one we fall back to
        // the first delivery date of the option, which is not a shopper nominated date.
        $nominatedDeliveryDate = $this->arrayManager->get('preferredDeliveryDate', $result);
        if (!$prefferedDeliveryDate = $nominatedDeliveryDate) {
            $prefferedDeliveryDate = $this->arrayManager->get('shippingOption/deliveryDates/0/deliveryDate', $result);
        }
        $info->setPreferredDeliveryDate($prefferedDeliveryDate);
        $info->setNominatedDate(!empty($nominatedDeliveryDate));

        $info->setEstimatedDeliveryRange($this->arrayManager->get('shippingOption/estimatedDeliveryRange', $result));

        if (!$pickupDate = $this->arrayManager->get('pickupDate', $result)) {
            $pickupDate = $this->arrayManager->get('shippingOption/deliveryDates/0/pickupDate', $result);
        }
        $info->setCarrierPickupDate($pickupDate);

        // Find rateReason for the selected delivery date
        $green = false;
        $deliveryDates = $this->arrayManager->get('shippingOption/deliveryDates', $result) ?? [];
        foreach ($deliveryDates as $deliveryDate) {
            if (isset($deliveryDate['deliveryDate'])
                && $deliveryDate['deliveryDate'] === $prefferedDeliveryDate
                && isset($deliveryDate['rateReason'])
                && $deliveryDate['rateReason'] === 'GREEN'
            ) {
                $green = true;
                break;
            }
        }
        $info->setGreen($green);

        if ($info->getType() === DeliveryType::PICKUP) {
            $info->setPickupDate($this->arrayManager->get('pickupDate', $result));
            $info->setLocationCode($this->arrayManager->get('pickupLocation/code', $result));
            $info->setLocationAccountNumber($this->arrayManager->get('pickupLocation/accountNumber', $result));
            $info->setLocationName($this->arrayManager->get('pickupLocation/name', $result));
            $info->setPickupAddress($this->arrayManager->get('pickupLocation/address', $result));
        }

        return $info;
    }
}
