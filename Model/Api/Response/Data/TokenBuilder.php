<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Api\Response\Data;

use Magento\Framework\Api\AbstractSimpleObjectBuilder;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class TokenBuilder
 *
 * @package Paazl\CheckoutWidget\Model\Api\Response\Data
 */
class TokenBuilder extends AbstractSimpleObjectBuilder
{
    /**
     * @param string $response
     * @return TokenBuilder
     * @throws LocalizedException
     */
    public function setResponse(string $response)
    {
        $token = json_decode($response);
        if (!$token->token) {
            throw new LocalizedException(__('Cannot parse token'));
        }

        $this->_set('token', $token->token);

        return $this;
    }
}
