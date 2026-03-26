<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\DeferredQueue;

use Magento\Framework\Model\AbstractModel;
use Paazl\CheckoutWidget\Api\Data\DeferredQueue\DeferredQueueInterface;
use Paazl\CheckoutWidget\Model\ResourceModel\DeferredQueue\DeferredQueue as DeferredQueueResource;

/**
 * Class DeferredQueue
 *
 * @package Paazl\CheckoutWidget\Model\DeferredQueue
 */
class DeferredQueue extends AbstractModel implements DeferredQueueInterface
{
    /**
     * Init resource model.
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(DeferredQueueResource::class);
    }

    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setEntityId($value)
    {
        return $this->setData(self::ENTITY_ID, $value);
    }

    /**
     * @inheritDoc
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderId($value)
    {
        return $this->setData(self::ORDER_ID, $value);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerName()
    {
        return $this->getData(self::CUSTOMER_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerName($value)
    {
        return $this->setData(self::CUSTOMER_NAME, $value);
    }

    /**
     * @inheritDoc
     */
    public function getScheduledProcessingDate()
    {
        return $this->getData(self::SCHEDULED_PROCESSING_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setScheduledProcessingDate($value)
    {
        return $this->setData(self::SCHEDULED_PROCESSING_DATE, $value);
    }

    /**
     * @inheritDoc
     */
    public function getDeferredStatus()
    {
        return $this->getData(self::DEFERRED_STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setDeferredStatus($value)
    {
        return $this->setData(self::DEFERRED_STATUS, $value);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($value)
    {
        return $this->setData(self::CREATED_AT, $value);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt($value)
    {
        return $this->setData(self::UPDATED_AT, $value);
    }
}
