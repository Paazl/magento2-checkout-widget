<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Plugin\Checkout;

use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Paazl\CheckoutWidget\Model\Api\Builder\Reference;
use Paazl\CheckoutWidget\Model\Api\PaazlApi;
use Paazl\CheckoutWidget\Model\Api\PaazlApiFactory;
use Paazl\CheckoutWidget\Model\Api\Processor\CheckoutInfoToQuote;
use Paazl\CheckoutWidget\Model\Carrier\Paazlshipping;
use Paazl\CheckoutWidget\Model\Api\ApiException;
use Paazl\CheckoutWidget\Model\Api\Field\DeliveryType;
use Paazl\CheckoutWidget\Model\Quote\Totals\AppendShippingMethods;

/**
 * Class ShippingInformationManagementPlugin
 *
 * @package Paazl\CheckoutWidget\Plugin\Checkout
 */
class ShippingInformationManagementPlugin
{
    /**
     * @var PaazlApiFactory
     */
    private $paazlApiFactory;

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
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var AppendShippingMethods
     */
    private $appendShippingMethods;

    /**
     * ShippingInformationManagementPlugin constructor.
     *
     * @param PaazlApiFactory                   $paazlApiFactory
     * @param Reference                         $referenceBuilder
     * @param CartRepositoryInterface           $quoteRepository
     * @param CheckoutInfoToQuote               $checkoutInfoToQuote
     * @param ShipmentEstimationInterface       $shipmentEstimation
     * @param ArrayManager                      $arrayManager
     * @param AppendShippingMethods             $appendShippingMethods
     */
    public function __construct(
        PaazlApiFactory $paazlApiFactory,
        Reference $referenceBuilder,
        CartRepositoryInterface $quoteRepository,
        CheckoutInfoToQuote $checkoutInfoToQuote,
        ShipmentEstimationInterface $shipmentEstimation,
        ArrayManager $arrayManager,
        AppendShippingMethods $appendShippingMethods
    ) {
        $this->paazlApiFactory = $paazlApiFactory;
        $this->referenceBuilder = $referenceBuilder;
        $this->quoteRepository = $quoteRepository;
        $this->checkoutInfoToQuote = $checkoutInfoToQuote;
        $this->shipmentEstimation = $shipmentEstimation;
        $this->arrayManager = $arrayManager;
        $this->appendShippingMethods = $appendShippingMethods;
    }

    /**
     * @param ShippingInformationManagement $subject
     * @param int                           $cartId
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
            /** @var PaazlApi $paazlApi */
            $paazlApi = $this->paazlApiFactory->create($quote->getStoreId());
            $result = $paazlApi->fetchCheckoutData($this->referenceBuilder->getQuoteReference($quote));

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
                    ->setCompany($this->arrayManager->get('name', $pickupLocation))
                    ->setSaveInAddressBook(0);

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
        }

        // Calling the observed method
        return null;
    }

    /**
     * @param ShippingInformationManagement $subject
     * @param PaymentDetailsInterface       $paymentDetails
     * @param int                           $cartId
     * @return PaymentDetailsInterface
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function afterSaveAddressInformation(
        ShippingInformationManagement $subject,
        PaymentDetailsInterface $paymentDetails,
        $cartId
    ) {
        $this->appendShippingMethods->append($paymentDetails->getTotals(), $cartId);

        return $paymentDetails;
    }
}
