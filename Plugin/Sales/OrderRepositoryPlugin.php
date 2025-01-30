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
use Paazl\CheckoutWidget\Api\QuoteReferenceRepositoryInterface;
use Paazl\CheckoutWidget\Model\Api\Processor\SendToService;
use Paazl\CheckoutWidget\Model\Carrier\Paazlshipping;
use Paazl\CheckoutWidget\Model\Order\OrderReference;
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
     * @var QuoteReferenceRepositoryInterface
     */
    private $quoteReferenceRepository;

    /**
     * OrderRepositoryPlugin constructor.
     *
     * @param OrderReferenceFactory             $orderReferenceFactory
     * @param CartRepositoryInterface           $cartRepository
     * @param SendToService                     $sendToService
     * @param GeneralHelper                     $generalHelper
     * @param OrderReferenceRepositoryInterface $orderReferenceRepository
     * @param QuoteReferenceRepositoryInterface $quoteReferenceRepository
     */
    public function __construct(
        OrderReferenceFactory $orderReferenceFactory,
        CartRepositoryInterface $cartRepository,
        SendToService $sendToService,
        GeneralHelper $generalHelper,
        OrderReferenceRepositoryInterface $orderReferenceRepository,
        QuoteReferenceRepositoryInterface $quoteReferenceRepository
    ) {
        $this->orderReferenceFactory = $orderReferenceFactory;
        $this->cartRepository = $cartRepository;
        $this->sendToService = $sendToService;
        $this->generalHelper = $generalHelper;
        $this->orderReferenceRepository = $orderReferenceRepository;
        $this->quoteReferenceRepository = $quoteReferenceRepository;
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
        if (!$this->isPaazlShipping($order)) {
            return $order;
        }

        try {
            // Check if an order reference already exists
            $this->orderReferenceRepository->getByOrderId($order->getId());
            return $order;
        } catch (NoSuchEntityException $e) {
            // No reference found, proceed to create one
        }

        $orderReference = $this->createOrderReference($order);

        if ($orderReference === null) {
            return $order;
        }
        $this->saveOrderReference($orderReference);
        return $order;
    }

    /**
     * Check if the order uses Paazl shipping.
     *
     * @param OrderInterface $order
     * @return bool
     */
    private function isPaazlShipping(OrderInterface $order): bool
    {
        $shippingMethod = $order->getShippingMethod(true);
        return $shippingMethod && $shippingMethod->getData('carrier_code') === Paazlshipping::CODE;
    }

    /**
     * Create an order reference from the quote reference.
     *
     * @param OrderInterface $order
     * @return OrderReferenceInterface|null
     */
    private function createOrderReference(OrderInterface $order): ?OrderReferenceInterface
    {
        try {
            $quoteReference = $this->quoteReferenceRepository->getByQuoteId($order->getQuoteId());
        } catch (NoSuchEntityException $e) {
            return null;
        }
        $orderReference = $this->orderReferenceFactory->create([
            'data' => [
                OrderReferenceInterface::ORDER_ID => $order->getId(),
            ],
        ]);
        $orderReference->setExtShippingInfo($quoteReference->getExtShippingInfo());
        return $orderReference;
    }

    /**
     * Save the order reference and log any exceptions.
     *
     * @param OrderReferenceInterface $orderReference
     * @return void
     */
    private function saveOrderReference(OrderReferenceInterface $orderReference): void
    {
        try {
            $this->orderReferenceRepository->save($orderReference);
        } catch (\Exception $e) {
            $this->generalHelper->addTolog('exception', $e->getMessage());
        }
    }
}
