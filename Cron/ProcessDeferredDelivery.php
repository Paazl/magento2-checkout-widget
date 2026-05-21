<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Cron;

use Exception;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Paazl\CheckoutWidget\Helper\General as GeneralHelper;
use Paazl\CheckoutWidget\Model\Config;
use Paazl\CheckoutWidget\Model\DeferredQueue\Processor;
use Paazl\CheckoutWidget\Model\ResourceModel\DeferredQueue\DeferredQueueCollectionFactory;

/**
 * Class ProcessDeferredDelivery
 *
 * @package Paazl\CheckoutWidget\Cron
 */
class ProcessDeferredDelivery
{
    public const DEFERRED_STATUS_PENDING = Processor::DEFERRED_STATUS_PENDING;
    public const DEFERRED_STATUS_PROCESSED = Processor::DEFERRED_STATUS_PROCESSED;

    private DeferredQueueCollectionFactory $deferredQueueCollectionFactory;
    private GeneralHelper $generalHelper;
    private Config $config;
    private DateTime $dateTime;
    private Processor $processor;

    public function __construct(
        DeferredQueueCollectionFactory $deferredQueueCollectionFactory,
        GeneralHelper $generalHelper,
        Config $config,
        DateTime $dateTime,
        Processor $processor
    ) {
        $this->deferredQueueCollectionFactory = $deferredQueueCollectionFactory;
        $this->generalHelper = $generalHelper;
        $this->config = $config;
        $this->dateTime = $dateTime;
        $this->processor = $processor;
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
                if ($this->processor->process($deferredQueue)) {
                    $processedCount++;
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
