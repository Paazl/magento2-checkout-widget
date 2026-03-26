<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Api\Data\DeferredQueue;

/**
 * Interface DeferredQueueInterface
 * Declaration of the Paazl Deferred Queue
 *
 * @package Paazl\CheckoutWidget\Api\Data\DeferredQueue
 */
interface DeferredQueueInterface
{
    /**#@+
     * Indexes of fields
     *
     * @var string
     */
    public const ENTITY_ID = 'entity_id';
    public const ORDER_ID = 'order_id';
    public const CUSTOMER_NAME = 'customer_name';
    public const SCHEDULED_PROCESSING_DATE = 'scheduled_processing_date';
    public const DEFERRED_STATUS = 'deferred_status';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    /**#@-*/

    /**
     * @return int|null
     */
    public function getEntityId();

    /**
     * @param int $value
     * @return $this
     */
    public function setEntityId($value);

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @param int $value
     * @return $this
     */
    public function setOrderId($value);

    /**
     * @return string|null
     */
    public function getCustomerName();

    /**
     * @param string $value
     * @return $this
     */
    public function setCustomerName($value);

    /**
     * @return string|null
     */
    public function getScheduledProcessingDate();

    /**
     * @param string $value
     * @return $this
     */
    public function setScheduledProcessingDate($value);

    /**
     * @return string|null
     */
    public function getDeferredStatus();

    /**
     * @param string $value
     * @return $this
     */
    public function setDeferredStatus($value);

    /**
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @param string $value
     * @return $this
     */
    public function setCreatedAt($value);

    /**
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * @param string $value
     * @return $this
     */
    public function setUpdatedAt($value);
}
