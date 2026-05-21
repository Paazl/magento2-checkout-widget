<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Model\DeferredQueue;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Paazl\CheckoutWidget\Helper\General as GeneralHelper;
use Paazl\CheckoutWidget\Model\ResourceModel\DeferredQueue\DeferredQueue as DeferredQueueResource;
use Paazl\CheckoutWidget\Plugin\Sales\DeferredDeliveryPlugin;

/**
 * Transitions a deferred-queue row's order to Processing and marks the row
 * processed. Shared by the scheduled cron and the admin "Process Now" action.
 */
class Processor
{
    public const DEFERRED_STATUS_PENDING = 'pending';
    public const DEFERRED_STATUS_PROCESSED = 'processed';

    private DeferredQueueFactory $deferredQueueFactory;
    private DeferredQueueResource $deferredQueueResource;
    private OrderRepositoryInterface $orderRepository;
    private GeneralHelper $generalHelper;

    public function __construct(
        DeferredQueueFactory $deferredQueueFactory,
        DeferredQueueResource $deferredQueueResource,
        OrderRepositoryInterface $orderRepository,
        GeneralHelper $generalHelper
    ) {
        $this->deferredQueueFactory = $deferredQueueFactory;
        $this->deferredQueueResource = $deferredQueueResource;
        $this->orderRepository = $orderRepository;
        $this->generalHelper = $generalHelper;
    }

    /**
     * Transition the queued row's order to Processing.
     *
     * @param DeferredQueue $deferredQueue Already-loaded queue row.
     * @param string|null $manualNote When set, used as the status-history note
     *                                instead of the automatic one (admin trigger).
     * @return bool True when the order was transitioned, false when skipped
     *              (wrong state, missing order, already processed).
     */
    public function process(DeferredQueue $deferredQueue, ?string $manualNote = null): bool
    {
        if ($deferredQueue->getDeferredStatus() === self::DEFERRED_STATUS_PROCESSED) {
            return false;
        }

        $orderId = $deferredQueue->getOrderId();
        if (!$orderId) {
            return false;
        }

        $order = $this->orderRepository->get($orderId);

        if ($order->getState() !== Order::STATE_NEW
            || $order->getStatus() !== DeferredDeliveryPlugin::DEFERRED_DELIVERY_STATUS
        ) {
            return false;
        }

        $order->setState(Order::STATE_PROCESSING);
        $order->setStatus(Order::STATE_PROCESSING);
        $order->addCommentToStatusHistory(
            $manualNote !== null
                ? __($manualNote)
                : __('Order automatically transitioned from Deferred Delivery to Processing status.')
        );
        $this->orderRepository->save($order);

        $deferredQueue->setDeferredStatus(self::DEFERRED_STATUS_PROCESSED);
        $this->deferredQueueResource->save($deferredQueue);

        $this->generalHelper->addTolog('info', sprintf(
            'Order #%s transitioned to Processing status',
            $order->getIncrementId()
        ));

        return true;
    }

    /**
     * Convenience wrapper used by the admin action: load by ID, then process.
     *
     * @throws LocalizedException When the row does not exist.
     */
    public function processById(int $deferredQueueId, ?string $manualNote = null): bool
    {
        $deferredQueue = $this->deferredQueueFactory->create();
        $this->deferredQueueResource->load($deferredQueue, $deferredQueueId);
        if (!$deferredQueue->getEntityId()) {
            throw new LocalizedException(__('Deferred queue entry not found.'));
        }
        return $this->process($deferredQueue, $manualNote);
    }
}
