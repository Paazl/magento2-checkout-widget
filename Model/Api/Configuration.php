<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Api;

use Paazl\CheckoutWidget\Model\System\Config\Source\ApiMode;

/**
 * Configuration provider for the API client.
 */
class Configuration
{
    /**
     * @var int
     */
    private $timeout = 300;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var int
     */
    private $mode = ApiMode::MODE_STAGING;

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     *
     * @return Configuration
     */
    public function setTimeout(int $timeout): Configuration
    {
        if ($timeout > 0) {
            $this->timeout = $timeout;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     *
     * @return Configuration
     */
    public function setKey(string $key): Configuration
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     *
     * @return Configuration
     */
    public function setSecret(string $secret): Configuration
    {
        $this->secret = $secret;
        return $this;
    }

    /**
     * @return int
     */
    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * @param int $mode
     *
     * @return Configuration
     */
    public function setMode(int $mode): Configuration
    {
        $this->mode = $mode;
        return $this;
    }
}
