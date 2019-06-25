<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Plugin\Sales;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository;
use Paazl\CheckoutWidget\Api\Data\Order\OrderReferenceInterface;
use Paazl\CheckoutWidget\Api\OrderReferenceRepositoryInterface;
use Paazl\CheckoutWidget\Model\Api\Processor\SendToService;
use Paazl\CheckoutWidget\Model\Carrier\Paazlshipping;
use Paazl\CheckoutWidget\Model\Order\OrderReferenceFactory;
use Paazl\CheckoutWidget\Helper\General as GeneralHelper;

/**
 * We have to use plugin.
 * Observer sales_order_place_after doesn't work - order is not saved yet.
 *
 * @package Paazl\CheckoutWidget\Plugin\Sales
 */
class OrderRepositoryPlugin
{
    /**
     * @var OrderReferenceFactory
     */
    private $orderReferenceFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var SendToService
     */
    private $sendToService;

    /**
     * @var GeneralHelper
     */
    private $generalHelper;

    /**
     * @var OrderReferenceRepositoryInterface
     */
    private $orderReferenceRepository;

    /**
     * OrderRepositoryPlugin constructor.
     *
     * @param OrderReferenceFactory             $orderReferenceFactory
     * @param CartRepositoryInterface           $cartRepository
     * @param SendToService                     $sendToService
     * @param GeneralHelper                     $generalHelper
     * @param OrderReferenceRepositoryInterface $orderReferenceRepository
     */
    public function __construct(
        OrderReferenceFactory $orderReferenceFactory,
        CartRepositoryInterface $cartRepository,
        SendToService $sendToService,
        GeneralHelper $generalHelper,
        OrderReferenceRepositoryInterface $orderReferenceRepository
    ) {
        $this->orderReferenceFactory = $orderReferenceFactory;
        $this->cartRepository = $cartRepository;
        $this->sendToService = $sendToService;
        $this->generalHelper = $generalHelper;
        $this->orderReferenceRepository = $orderReferenceRepository;
    }

    /**
     * @param OrderRepository $subject
     * @param OrderInterface|\Magento\Sales\Model\Order $order
     * @return OrderInterface
     */
    public function afterSave(
        OrderRepository $subject,
        OrderInterface $order
    ) {
        $shippingMethod = $order->getShippingMethod(true);
        $carrierCode = $shippingMethod->getData('carrier_code');

        if ($carrierCode !== Paazlshipping::CODE) {
            return $order;
        }

        try {
            $this->orderReferenceRepository->getByOrderId($order->getId());
            return $order;
        } catch (NoSuchEntityException $e) {
            // Reference not found
            $orderReference = $this->orderReferenceFactory->create(['data' => [
                OrderReferenceInterface::ORDER_ID => $order->getId(),
            ]]);
        }

        try {
            $quote = $this->cartRepository->get($order->getQuoteId());
        } catch (NoSuchEntityException $e) {
            return $order;
        }

        $orderReference->setExtShippingInfo($quote->getData('ext_shipping_info'));

        try {
            $this->orderReferenceRepository->save($orderReference);
            $this->sendToService->process($order);
        } catch (\Exception $e) {
            $this->generalHelper->addTolog('exception', $e->getMessage());
        }

        return $order;
    }
}
