<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Plugin\Checkout;

use Magento\Quote\Api\Data\TotalsExtensionFactory;
use Magento\Quote\Api\Data\TotalsExtensionInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Paazl\CheckoutWidget\Model\Api\Builder\Reference;
use Paazl\CheckoutWidget\Model\Api\PaazlApi;
use Paazl\CheckoutWidget\Model\Api\Processor\CheckoutInfoToQuote;
use Paazl\CheckoutWidget\Model\Carrier\Paazlshipping;
use Paazl\CheckoutWidget\Model\Api\ApiException;
use Paazl\CheckoutWidget\Model\Api\Field\DeliveryType;

/**
 * Class ShippingInformationManagementPlugin
 *
 * @package Paazl\CheckoutWidget\Plugin\Checkout
 */
class ShippingInformationManagementPlugin
{
    /**
     * @var PaazlApi
     */
    private $api;

    /**
     * @var Reference
     */
    private $referenceBuilder;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CheckoutInfoToQuote
     */
    private $checkoutInfoToQuote;

    /**
     * @var ShipmentEstimationInterface
     */
    private $shipmentEstimation;

    /**
     * @var CartInterface
     */
    private $quote;

    /**
     * @var ShippingMethodManagementInterface
     */
    private $shippingMethodManagement;

    /**
     * @var TotalsExtensionFactory
     */
    private $totalsExtensionFactory;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * ShippingInformationManagementPlugin constructor.
     *
     * @param PaazlApi                          $api
     * @param Reference                         $referenceBuilder
     * @param CartRepositoryInterface           $quoteRepository
     * @param CheckoutInfoToQuote               $checkoutInfoToQuote
     * @param ShipmentEstimationInterface       $shipmentEstimation
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param TotalsExtensionFactory            $totalsExtensionFactory
     * @param ArrayManager                      $arrayManager
     */
    public function __construct(
        PaazlApi $api,
        Reference $referenceBuilder,
        CartRepositoryInterface $quoteRepository,
        CheckoutInfoToQuote $checkoutInfoToQuote,
        ShipmentEstimationInterface $shipmentEstimation,
        ShippingMethodManagementInterface $shippingMethodManagement,
        TotalsExtensionFactory $totalsExtensionFactory,
        ArrayManager $arrayManager
    ) {
        $this->api = $api;
        $this->referenceBuilder = $referenceBuilder;
        $this->quoteRepository = $quoteRepository;
        $this->checkoutInfoToQuote = $checkoutInfoToQuote;
        $this->shipmentEstimation = $shipmentEstimation;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->totalsExtensionFactory = $totalsExtensionFactory;
        $this->arrayManager = $arrayManager;
    }

    /**
     * @param ShippingInformationManagement $subject
     * @param                               $cartId
     * @param ShippingInformationInterface  $addressInformation
     *
     * @return null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws ApiException
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $quote = $this->quoteRepository->getActive($cartId);

        if ($addressInformation->getShippingCarrierCode() === Paazlshipping::CODE) {
            // Getting the checkout information from Paazl
            $result = $this->api->fetchCheckoutData($this->referenceBuilder->getQuoteReference($quote));

            // Checking pickupLocation
            if ($this->arrayManager->get('deliveryType', $result) === DeliveryType::PICKUP) {
                $pickupLocation = $this->arrayManager->get('pickupLocation', $result);
                $pickupLocationAddress = $this->arrayManager->get('address', $pickupLocation);

                // ... and set to address information
                $street = [];
                $street[] = $pickupLocationAddress['street'];
                $street[] = $pickupLocationAddress['streetNumber'] ?? '';
                $pickupLocationAddress = $this->arrayManager->remove('streetNumber', $pickupLocationAddress);
                $pickupLocationAddress['street'] = rtrim(implode(' ', $street));
                $addressInformation->getShippingAddress()
                    ->setCustomerAddressId(null)
                    ->setPostcode($this->arrayManager->get('postalCode', $pickupLocationAddress))
                    ->setStreet($this->arrayManager->get('street', $pickupLocationAddress))
                    ->setCity($this->arrayManager->get('city', $pickupLocationAddress))
                    ->setCountryId($this->arrayManager->get('country', $pickupLocationAddress))
                    ->setCompany($this->arrayManager->get('name', $pickupLocation));

                // ... also set to quote shipping address if exists
                if (($quoteShippingAddress = $quote->getShippingAddress())
                    && $quoteShippingAddress->getId()
                ) {
                    $quoteShippingAddress->setCustomerAddressId(null)
                        ->setPostcode($this->arrayManager->get('postalCode', $pickupLocationAddress))
                        ->setStreet($this->arrayManager->get('street', $pickupLocationAddress))
                        ->setCity($this->arrayManager->get('city', $pickupLocationAddress))
                        ->setCountryId($this->arrayManager->get('country', $pickupLocationAddress))
                        ->setCompany($this->arrayManager->get('name', $pickupLocation))
                        ->save();
                }
            }

            $this->checkoutInfoToQuote->process($quote);
            $this->quote = $quote;
        }

        // Calling the observed method
        return null;
    }

    /**
     * @param ShippingInformationManagement $subject
     * @param PaymentDetailsInterface       $paymentDetails
     *
     * @return PaymentDetailsInterface
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function afterSaveAddressInformation(
        ShippingInformationManagement $subject,
        PaymentDetailsInterface $paymentDetails
    ) {
        if ($this->quote && (!$this->quote->getIsVirtual())) {
            // Adding the list of shipping methods to output
            $methods = $this->shippingMethodManagement->getList($this->quote->getId());

            $isPaazlShippingFlag = false;

            foreach ($methods as $method) {
                if ($method->getCarrierCode() === Paazlshipping::CODE) {
                    $isPaazlShippingFlag = true;
                }
            }

            if (!$isPaazlShippingFlag) {
                return $paymentDetails;
            }

            /** @var TotalsInterface $totals */
            $totals = $paymentDetails->getTotals();

            /** @var TotalsExtensionInterface $extension */
            $extension = $totals->getExtensionAttributes();
            if (!$extension) {
                $extension = $this->totalsExtensionFactory->create();
            }
            $extension->setShippingMethods($methods);
            $totals->setExtensionAttributes($extension);
        }

        return $paymentDetails;
    }
}
