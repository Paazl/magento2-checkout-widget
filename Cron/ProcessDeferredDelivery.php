<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Cron;

use Exception;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Paazl\CheckoutWidget\Helper\General as GeneralHelper;
use Paazl\CheckoutWidget\Model\Config;
use Paazl\CheckoutWidget\Model\ResourceModel\DeferredQueue\DeferredQueue as DeferredQueueResource;
use Paazl\CheckoutWidget\Model\ResourceModel\DeferredQueue\DeferredQueueCollectionFactory;

/**
 * Class ProcessDeferredDelivery
 *
 * @package Paazl\CheckoutWidget\Cron
 */
class ProcessDeferredDelivery
{
    public const DEFERRED_STATUS_PENDING = 'pending';
    public const DEFERRED_STATUS_PROCESSED = 'processed';

    private DeferredQueueCollectionFactory $deferredQueueCollectionFactory;
    private OrderRepositoryInterface $orderRepository;
    private GeneralHelper $generalHelper;
    private Config $config;
    private DateTime $dateTime;
    private DeferredQueueResource $deferredQueueResource;

    /**
     * ProcessDeferredDelivery constructor.
     *
     * @param DeferredQueueCollectionFactory $deferredQueueCollectionFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param GeneralHelper $generalHelper
     * @param Config $config
     * @param DateTime $dateTime
     * @param DeferredQueueResource $deferredQueueResource
     */
    public function __construct(
        DeferredQueueCollectionFactory $deferredQueueCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        GeneralHelper $generalHelper,
        Config $config,
        DateTime $dateTime,
        DeferredQueueResource $deferredQueueResource
    ) {
        $this->deferredQueueCollectionFactory = $deferredQueueCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->generalHelper = $generalHelper;
        $this->config = $config;
        $this->dateTime = $dateTime;
        $this->deferredQueueResource = $deferredQueueResource;
    }

    /**
     * Process deferred delivery orders
     *
     * @return array
     */
    public function execute()
    {
        if (!$this->config->isDeferredDeliveryEnabled()) {
            return [];
        }

        $daysBeforePickup = $this->config->getDeferredDeliveryDaysBefore();
        $targetDate = $this->calculateTargetDate($daysBeforePickup);

        $this->generalHelper->addTolog('info', sprintf(
            'Processing deferred delivery orders with scheduled pickup date before or on: %s',
            $targetDate
        ));

        $collection = $this->deferredQueueCollectionFactory->create();
        $collection->addFieldToFilter('scheduled_processing_date', ['lteq' => $targetDate])
            ->addFieldToFilter('deferred_status', ['eq' => self::DEFERRED_STATUS_PENDING]);

        $processedCount = 0;
        foreach ($collection as $deferredQueue) {
            try {
                $orderId = $deferredQueue->getOrderId();
                $order = $this->orderRepository->get($orderId);

                if ($order->getState() === Order::STATE_NEW &&
                    $order->getStatus() === 'deferred_delivery') {

                    $order->setState(Order::STATE_PROCESSING);
                    $order->setStatus(Order::STATE_PROCESSING);
                    $order->addCommentToStatusHistory(
                        __('Order automatically transitioned from Deferred Delivery to Processing status.')
                    );

                    $this->orderRepository->save($order);

                    $deferredQueue->setDeferredStatus(self::DEFERRED_STATUS_PROCESSED);
                    $this->deferredQueueResource->save($deferredQueue);

                    $processedCount++;

                    $this->generalHelper->addTolog('info', sprintf(
                        'Order #%s transitioned to Processing status',
                        $order->getIncrementId()
                    ));
                }
            } catch (Exception $e) {
                $this->generalHelper->addTolog('exception', sprintf(
                    'Error processing deferred order ID %s: %s',
                    $deferredQueue->getOrderId(),
                    $e->getMessage()
                ));
            }
        }

        $this->generalHelper->addTolog('info', sprintf(
            'Processed %d deferred delivery order(s)',
            $processedCount
        ));

        return [];
    }

    /**
     * Calculate target date based on days before pickup
     *
     * @param int $daysBeforePickup
     * @return string
     */
    private function calculateTargetDate($daysBeforePickup)
    {
        $timestamp = $this->dateTime->gmtTimestamp();
        $targetTimestamp = $timestamp + ($daysBeforePickup * 86400); // 86400 seconds in a day
        return date('Y-m-d H:i:s', $targetTimestamp);
    }
}
