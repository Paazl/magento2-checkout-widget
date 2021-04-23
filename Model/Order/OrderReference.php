<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Order;

use Magento\Framework\Model\AbstractModel;
use Paazl\CheckoutWidget\Api\Data\Order\OrderReferenceInterface;
use Paazl\CheckoutWidget\Model\ResourceModel\Order\OrderReference as OrderReferenceResource;

/**
 * Class OrderReference
 *
 * @package Paazl\CheckoutWidget\Model\Order
 */
class OrderReference extends AbstractModel implements OrderReferenceInterface
{
    /**
     * Init resource model.
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(OrderReferenceResource::class);
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setOrderId($value)
    {
        return $this->setData(self::ORDER_ID, $value);
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setExtShippingInfo($value)
    {
        return $this->setData(self::EXT_SHIPPING_INFO, $value);
    }

    /**
     * @return string
     */
    public function getExtShippingInfo()
    {
        return $this->getData(self::EXT_SHIPPING_INFO);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setExtSentAt($value)
    {
        return $this->setData(self::EXT_SENT_AT, $value);
    }

    /**
     * @return string
     */
    public function getExtSentAt()
    {
        return $this->getData(self::EXT_SENT_AT);
    }

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setInvalid($value)
    {
        return $this->setData(self::EXT_INVALID, $value);
    }

    /**
     * @return int
     */
    public function getInvalid()
    {
        return (int)$this->getData(self::EXT_INVALID);
    }

    /**
     * @return bool
     */
    public function isSent()
    {
        return (!empty($this->getExtSentAt()));
    }

    /**
     * @inheritDoc
     */
    public function canResend()
    {
        return $this->isSent();
    }

    /**
     * @inheritDoc
     */
    public function canRetry()
    {
        return (!$this->isSent()) && (!empty($this->getExtShippingInfo()));
    }

    /**
     * @inheritDoc
     */
    public function canEdit()
    {
        return !$this->isSent();
    }
}
