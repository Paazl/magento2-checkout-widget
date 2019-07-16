<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Api\Data\Quote;

/**
 * Interface QuoteReferenceInterface
 *
 * @package Paazl\CheckoutWidget\Api\Data\Quote
 */
interface QuoteReferenceInterface
{

    /**#@+
     * Indexes of fields
     *
     * @var string
     */
    const ENTITY_ID = 'entity_id';
    const QUOTE_ID = 'quote_id';
    const EXT_SHIPPING_INFO = 'ext_shipping_info';
    const TOKEN = 'token';
    const TOKEN_EXPIRES_AT = 'token_expires_at';
    /**#@-*/

    /**
     * @return int|null
     */
    public function getQuoteId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setQuoteId(int $value);

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
    public function setToken($value);

    /**
     * @return string
     */
    public function getToken();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setTokenExpiresAt($value);

    /**
     * @return string
     */
    public function getTokenExpiresAt();
}
