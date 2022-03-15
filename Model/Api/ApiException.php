<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Api;

use Throwable;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ApiException
 *
 * @package Paazl\CheckoutWidget\Model\Api
 */
class ApiException extends \Exception
{

    public function __construct(
        $message = "",
        $code = 0,
        Throwable $previous = null,
        $response = false
    ) {
        if ($response) {
            $code = (int)$code;
            $errors = json_decode($message, true);
            $message = 'API error';
            if ($errors !== null && !empty($errors['errors']) && is_array($errors['errors'])) {
                $messages = array_map(function ($error) {
                    return isset($error['message']) ? $error['message'] : null;
                }, $errors['errors']);

                $message = implode(', ', $messages);
            }
        }
        parent::__construct($message, $code, $previous);
    }
}
