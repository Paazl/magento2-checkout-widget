<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Api\CheckoutSelection;

/**
 * Checkout Selection data interface
 */
interface DataInterface
{
    public const ENTITY_ID         = 'entity_id';
    public const QUOTE_ID          = 'quote_id';
    public const EXT_SHIPPING_INFO = 'ext_shipping_info';
    public const WAS_SENT          = 'was_sent';
    public const SENT_AT           = 'sent_at';
    public const NEXT_TO_PAYMENT   = 'next_to_payment';
    public const IS_FINAL          = 'is_final';
    public const PICKUP_OFFERED    = 'pickup_offered';
    public const CREATED_AT        = 'created_at';
    public const UPDATED_AT        = 'updated_at';

    /**
     * Get entity ID
     */
    public function getEntityId();

    /**
     * Set entity ID
     *
     * @param $entityId
     * @return $this
     */
    public function setEntityId($entityId): self;

    /**
     * Get quote ID
     *
     * @return int
     */
    public function getQuoteId(): int;

    /**
     * Set quote ID
     *
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteId(int $quoteId): self;

    /**
     * Get external shipping info (JSON or free text)
     *
     * @return array|null
     */
    public function getExtShippingInfo(): ?array;

    /**
     * Set external shipping info (JSON or free text)
     *
     * @param array|null $extShippingInfo
     * @return $this
     */
    public function setExtShippingInfo(?array $extShippingInfo): self;

    /**
     * @return bool
     */
    public function getWasSent(): bool;

    /**
     * @param bool $wasSent
     * @return $this
     */
    public function setWasSent(bool $wasSent): self;

    /**
     * @return bool
     */
    public function getNextToPayment(): bool;

    /**
     * @param bool $nextToPayment
     * @return $this
     */
    public function setNextToPayment(bool $nextToPayment): self;

    /**
     * @return bool
     */
    public function getIsFinal(): bool;

    /**
     * @param bool $isFinal
     * @return $this
     */
    public function setIsFinal(bool $isFinal): self;

    /**
     * @return bool
     */
    public function getPickupOffered(): bool;

    /**
     * @param bool $pickupOffered
     * @return $this
     */
    public function setPickupOffered(bool $pickupOffered): self;
}
