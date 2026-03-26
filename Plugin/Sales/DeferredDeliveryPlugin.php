<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Plugin\Sales;

use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Paazl\CheckoutWidget\Api\OrderReferenceRepositoryInterface;
use Paazl\CheckoutWidget\Helper\General as GeneralHelper;
use Paazl\CheckoutWidget\Model\Carrier\Paazlshipping;
use Paazl\CheckoutWidget\Model\Config;
use Paazl\CheckoutWidget\Model\DeferredQueue\DeferredQueue;
use Paazl\CheckoutWidget\Model\DeferredQueue\DeferredQueueFactory;
use Paazl\CheckoutWidget\Model\ResourceModel\DeferredQueue\DeferredQueue as DeferredQueueResource;

/**
 * Plugin to handle deferred delivery orders
 *
 * @package Paazl\CheckoutWidget\Plugin\Sales
 */
class DeferredDeliveryPlugin
{
    public const DEFERRED_DELIVERY_STATUS = 'deferred_delivery';
    public const DEFERRED_STATUS_PENDING = 'pending';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var OrderReferenceRepositoryInterface
     */
    private $orderReferenceRepository;

    /**
     * @var GeneralHelper
     */
    private $generalHelper;

    /**
     * @var DeferredQueueFactory
     */
    private $deferredQueueFactory;

    /**
     * @var DeferredQueueResource
     */
    private $deferredQueueResource;

    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * @var array
     */
    private $processingOrders = [];

    /**
     * DeferredDeliveryPlugin constructor.
     *
     * @param Config $config
     * @param OrderReferenceRepositoryInterface $orderReferenceRepository
     * @param GeneralHelper $generalHelper
     * @param DeferredQueueFactory $deferredQueueFactory
     * @param DeferredQueueResource $deferredQueueResource
     * @param OrderResource $orderResource
     */
    public function __construct(
        Config $config,
        OrderReferenceRepositoryInterface $orderReferenceRepository,
        GeneralHelper $generalHelper,
        DeferredQueueFactory $deferredQueueFactory,
        DeferredQueueResource $deferredQueueResource,
        OrderResource $orderResource
    ) {
        $this->config = $config;
        $this->orderReferenceRepository = $orderReferenceRepository;
        $this->generalHelper = $generalHelper;
        $this->deferredQueueFactory = $deferredQueueFactory;
        $this->deferredQueueResource = $deferredQueueResource;
        $this->orderResource = $orderResource;
    }

    /**
     * Process deferred delivery orders after order save
     *
     * @param OrderRepository $subject
     * @param OrderInterface|Order $result
     * @return OrderInterface
     */
    public function afterSave(
        OrderRepository $subject,
        OrderInterface $result
    ) {
        if (!$this->config->isDeferredDeliveryEnabled()) {
            return $result;
        }

        if (!$this->isPaazlShipping($result)) {
            return $result;
        }

        if ($result->getState() !== Order::STATE_NEW) {
            return $result;
        }

        // Skip if already processing this order to prevent recursion
        $orderId = $result->getEntityId();
        if (isset($this->processingOrders[$orderId])) {
            return $result;
        }

        // Skip if already set to deferred delivery status
        if ($result->getStatus() === self::DEFERRED_DELIVERY_STATUS) {
            return $result;
        }

        try {
            $orderReference = $this->orderReferenceRepository->getByOrderId($result->getId());
            $shippingInfo = $orderReference->getExtShippingInfo();

            if ($this->isDeferredDeliveryOrder($shippingInfo)) {
                $this->processDeferredDelivery($result, $shippingInfo);
            }
        } catch (NoSuchEntityException $e) {
            // No order reference found, skip
        } catch (Exception $e) {
            $this->generalHelper->addTolog('exception', sprintf(
                'Error processing deferred delivery for order #%s: %s',
                $result->getIncrementId(),
                $e->getMessage()
            ));
        }

        return $result;
    }

    /**
     * Check if the order uses Paazl shipping
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
     * Check if this is a deferred delivery order
     *
     * @param string|null $shippingInfo
     * @return bool
     */
    private function isDeferredDeliveryOrder(?string $shippingInfo): bool
    {
        if (!$shippingInfo) {
            return false;
        }

        $info = json_decode($shippingInfo, true);
        if (!is_array($info)) {
            return false;
        }

        return isset($info['carrier_pickup_date']) && !empty($info['carrier_pickup_date']);
    }

    /**
     * Process deferred delivery order
     *
     * @param Order $order
     * @param string $shippingInfo
     * @return void
     * @throws Exception
     */
    private function processDeferredDelivery(Order $order, string $shippingInfo): void
    {
        $orderId = $order->getEntityId();
        $this->processingOrders[$orderId] = true;

        try {
            $info = json_decode($shippingInfo, true);
            $scheduledDate = $info['carrier_pickup_date'];

            $order->setState(Order::STATE_NEW);
            $order->setStatus(self::DEFERRED_DELIVERY_STATUS);
            $order->addCommentToStatusHistory(
                __('Order set to Deferred Delivery status. Scheduled pickup date: %1', $scheduledDate)
            );
            $this->orderResource->save($order);

            $this->addToDeferredQueue($order, $scheduledDate);

            $this->generalHelper->addTolog('info', sprintf(
                'Order #%s set to deferred delivery with scheduled pickup date: %s',
                $order->getIncrementId(),
                $scheduledDate
            ));
        } finally {
            unset($this->processingOrders[$orderId]);
        }
    }

    /**
     * Add order to deferred queue
     *
     * @param Order $order
     * @param string $scheduledDate
     * @return void
     * @throws Exception
     */
    private function addToDeferredQueue(Order $order, string $scheduledDate): void
    {
        $customerName = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();

        /** @var DeferredQueue $deferredQueue */
        $deferredQueue = $this->deferredQueueFactory->create();
        $deferredQueue->setOrderId($order->getId());
        $deferredQueue->setCustomerName($customerName);
        $deferredQueue->setScheduledProcessingDate($scheduledDate);
        $deferredQueue->setDeferredStatus(self::DEFERRED_STATUS_PENDING);

        $this->deferredQueueResource->save($deferredQueue);
    }
}
