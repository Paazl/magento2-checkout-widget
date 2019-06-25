<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Api;

use Paazl\CheckoutWidget\Model\Config;

class UrlProvider
{

    /**#@+
     * @var string
     */
    private const BASE_URL_LIVE = 'https://api.paazl.com/v1/';
    private const BASE_URL_TEST = 'https://api-acc.paazl.com/v1/';
    /**#@- */

    /**
     * @var Config
     */
    private $config;

    /**
     * UrlProvider constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        $result = self::BASE_URL_TEST;
        if ($this->config->isProductionApiMode()) {
            $result = self::BASE_URL_LIVE;
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getCheckoutUrl(): string
    {
        return $this->getBaseUrl() . 'checkout/';
    }

    /**
     * @return string
     */
    public function getCheckoutTokenUrl(): string
    {
        return $this->getBaseUrl() . 'checkout/token/';
    }

    /**
     * @return string
     */
    public function getOrderUrl(): string
    {
        return $this->getBaseUrl() . 'order/';
    }
}
