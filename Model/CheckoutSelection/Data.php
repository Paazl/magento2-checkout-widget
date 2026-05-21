<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Model\CheckoutSelection;

use Magento\Framework\Model\AbstractModel;
use Paazl\CheckoutWidget\Api\CheckoutSelection\DataInterface as CheckoutSelection;

/**
 * Checkout Selection data model
 */
class Data extends AbstractModel implements CheckoutSelection
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID) !== null
            ? (int)$this->getData(self::ENTITY_ID)
            : null;
    }

    /**
     * @inheritDoc
     */
    public function setEntityId($entityId): CheckoutSelection
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * @inheritDoc
     */
    public function getQuoteId(): int
    {
        return (int)$this->getData(self::QUOTE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setQuoteId(int $quoteId): CheckoutSelection
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * @inheritDoc
     */
    public function getExtShippingInfo(): ?array
    {
        return $this->getData(self::EXT_SHIPPING_INFO);
    }

    /**
     * @inheritDoc
     */
    public function setExtShippingInfo(?array $extShippingInfo): CheckoutSelection
    {
        return $this->setData(self::EXT_SHIPPING_INFO, $extShippingInfo);
    }

    /**
     * @inheritDoc
     */
    public function getWasSent(): bool
    {
        return (bool)$this->getData(self::WAS_SENT);
    }

    /**
     * @inheritDoc
     */
    public function setWasSent(bool $wasSent): CheckoutSelection
    {
        return $this->setData(self::WAS_SENT, $wasSent);
    }

    /**
     * @inheritDoc
     */
    public function getNextToPayment(): bool
    {
        return (bool)$this->getData(self::NEXT_TO_PAYMENT);
    }

    /**
     * @inheritDoc
     */
    public function setNextToPayment(bool $nextToPayment): CheckoutSelection
    {
        return $this->setData(self::NEXT_TO_PAYMENT, $nextToPayment);
    }

    /**
     * @inheritDoc
     */
    public function getIsFinal(): bool
    {
        return (bool)$this->getData(self::IS_FINAL);
    }

    /**
     * @inheritDoc
     */
    public function setIsFinal(bool $isFinal): CheckoutSelection
    {
        return $this->setData(self::IS_FINAL, $isFinal);
    }

    /**
     * @inheritDoc
     */
    public function getPickupOffered(): bool
    {
        return (bool)$this->getData(self::PICKUP_OFFERED);
    }

    /**
     * @inheritDoc
     */
    public function setPickupOffered(bool $pickupOffered): CheckoutSelection
    {
        return $this->setData(self::PICKUP_OFFERED, $pickupOffered);
    }
}
