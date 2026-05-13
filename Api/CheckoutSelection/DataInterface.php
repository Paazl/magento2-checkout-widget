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
     * @return DataInterface
     */
    public function setEntityId($entityId): DataInterface;

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
     * @return DataInterface
     */
    public function setQuoteId(int $quoteId): DataInterface;

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
     * @return DataInterface
     */
    public function setExtShippingInfo(?array $extShippingInfo): DataInterface;

    /**
     * @return bool
     */
    public function getWasSent(): bool;

    /**
     * @param bool $wasSent
     * @return DataInterface
     */
    public function setWasSent(bool $wasSent): DataInterface;

    /**
     * @return bool
     */
    public function getNextToPayment(): bool;

    /**
     * @param bool $nextToPayment
     * @return DataInterface
     */
    public function setNextToPayment(bool $nextToPayment): DataInterface;

    /**
     * @return bool
     */
    public function getIsFinal(): bool;

    /**
     * @param bool $isFinal
     * @return DataInterface
     */
    public function setIsFinal(bool $isFinal): DataInterface;

    /**
     * @return bool
     */
    public function getPickupOffered(): bool;

    /**
     * @param bool $pickupOffered
     * @return DataInterface
     */
    public function setPickupOffered(bool $pickupOffered): DataInterface;
}
