<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Plugin\Checkout;

use Magento\Checkout\Api\Data\PaymentDetailsExtensionFactory;
use Magento\Checkout\Api\Data\PaymentDetailsExtensionInterface;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Paazl\CheckoutWidget\Model\Api\Processor\CheckoutInfoToQuote;
use Paazl\CheckoutWidget\Model\Carrier\Paazlshipping;

/**
 * Class ShippingInformationManagementPlugin
 *
 * @package Paazl\CheckoutWidget\Plugin\Checkout
 */
class ShippingInformationManagementPlugin
{

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
     * @var PaymentDetailsExtensionFactory
     */
    private $paymentDetailsExtensionFactory;

    /**
     * ShippingInformationManagementPlugin constructor.
     *
     * @param CartRepositoryInterface           $quoteRepository
     * @param CheckoutInfoToQuote               $checkoutInfoToQuote
     * @param ShipmentEstimationInterface       $shipmentEstimation
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param PaymentDetailsExtensionFactory    $paymentDetailsExtensionFactory
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        CheckoutInfoToQuote $checkoutInfoToQuote,
        ShipmentEstimationInterface $shipmentEstimation,
        ShippingMethodManagementInterface $shippingMethodManagement,
        PaymentDetailsExtensionFactory $paymentDetailsExtensionFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->checkoutInfoToQuote = $checkoutInfoToQuote;
        $this->shipmentEstimation = $shipmentEstimation;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->paymentDetailsExtensionFactory = $paymentDetailsExtensionFactory;
    }

    /**
     * @param ShippingInformationManagement $subject
     * @param                               $cartId
     * @param ShippingInformationInterface  $addressInformation
     *
     * @return null
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $quote = $this->quoteRepository->getActive($cartId);

        if ($addressInformation->getShippingCarrierCode() === Paazlshipping::CODE) {
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
            /** @var PaymentDetailsExtensionInterface $extension */
            $extension = $paymentDetails->getExtensionAttributes();
            if (!$extension) {
                $extension = $this->paymentDetailsExtensionFactory->create();
            }
            $extension->setShippingMethods($methods);
            $paymentDetails->setExtensionAttributes($extension);
        }

        return $paymentDetails;
    }
}
