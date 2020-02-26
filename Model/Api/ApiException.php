<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Api;

/**
 * Class ApiException
 *
 * @package Paazl\CheckoutWidget\Model\Api
 */
class ApiException extends \Exception
{
    /**
     * @param \Exception|null $previous
     * @return ApiException
     */
    public static function error(\Exception $previous = null)
    {
        return new static('API error', 0, $previous);
    }

    /**
     * @param string $message
     * @param int|string $code
     * @param \Exception|null $previous
     * @return ApiException
     */
    public static function fromErrorResponse($message, $code, \Exception $previous = null)
    {
        // Parsing errors
        $code = (int)$code;
        $errors = json_decode($message, true);
        $message = 'API error';
        if ($errors !== null && !empty($errors['errors']) && is_array($errors['errors'])) {
            $messages = array_map(function ($error) {
                return isset($error['message']) ? $error['message'] : null;
            }, $errors['errors']);

            $message = implode(', ', $messages);
        }

        return new static($message, $code, $previous);
    }
}
