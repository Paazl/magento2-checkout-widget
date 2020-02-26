<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Api;

use Paazl\CheckoutWidget\Model\System\Config\Source\ApiMode;

/**
 * UrlProvider
 */
class UrlProvider
{
    /**#@+
     * @var string
     */
    const BASE_URL_LIVE = 'https://api.paazl.com/v1/';
    const BASE_URL_TEST = 'https://api-acc.paazl.com/v1/';
    /**#@- */

    /**
     * @var int
     */
    private $mode;

    /**
     * UrlProvider constructor.
     *
     * @param int $mode
     */
    public function __construct($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        $result = self::BASE_URL_TEST;
        if ($this->mode === ApiMode::MODE_PRODUCTION) {
            $result = self::BASE_URL_LIVE;
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getBaseUrl() . 'checkout/';
    }

    /**
     * @return string
     */
    public function getCheckoutTokenUrl()
    {
        return $this->getBaseUrl() . 'checkout/token/';
    }

    /**
     * @return string
     */
    public function getOrderUrl()
    {
        return $this->getBaseUrl() . 'order/';
    }

    /**
     * Retrieves shipping options API endpoint
     *
     * @return string
     */
    public function getShippingOptionsUrl()
    {
        return $this->getBaseUrl() . 'shippingoptions/';
    }
}
