<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Api\Data\Order;

/**
 * Interface OrderReferenceInterface
 * Declaration of the Paazl reference to the order
 *
 * @package Paazl\CheckoutWidget\Api\Data\Order
 */
interface OrderReferenceInterface
{
    /**#@+
     * Indexes of fields
     *
     * @var string
     */
    public const ENTITY_ID = 'entity_id';
    public const ORDER_ID = 'order_id';
    public const EXT_SHIPPING_INFO = 'ext_shipping_info';
    public const EXT_SENT_AT = 'ext_sent_at';
    public const EXT_INVALID = 'invalid';
    /**#@-*/

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setOrderId($value);

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setExtShippingInfo($value);

    /**
     * @return string
     */
    public function getExtShippingInfo();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setExtSentAt($value);

    /**
     * @return string
     */
    public function getExtSentAt();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setInvalid($value);

    /**
     * @return int
     */
    public function getInvalid();

    /**
     * @return bool
     */
    public function isSent();

    /**
     * Can resend order again after successful send.
     *
     * @return bool
     */
    public function canResend();

    /**
     * Can resend order again after failed send.
     *
     * @return bool
     */
    public function canRetry();

    /**
     * @return bool
     */
    public function canEdit();
}
