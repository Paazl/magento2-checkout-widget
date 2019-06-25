<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Api\Response\Data;

use Magento\Framework\DataObject;

/**
 * Class Token
 *
 * @package Paazl\CheckoutWidget\Model\Api\Response\Data
 */
class Token extends DataObject
{

    /**
     * @var string
     */
    const TOKEN = 'token';

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->getData(self::TOKEN);
    }

    /**
     * @param string $token
     *
     * @return Token
     */
    public function setToken($token)
    {
        return $this->setData(self::TOKEN, $token);
    }
}
