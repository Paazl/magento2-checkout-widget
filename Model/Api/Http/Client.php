<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Api\Http;

use Paazl\CheckoutWidget\Model\Api\CurlExtra;

/**
 * Extension of the core's HTTP client - we need PUT method
 */
class Client extends CurlExtra
{
    private $skipNextHeader = false;

    /**
     * Parse headers - CURL callback function
     *
     * @param resource $ch curl handle, not needed
     * @param string $data
     * @return int
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function parseHeaders($ch, $data)
    {
        if ($this->skipNextHeader) {
            $this->skipNextHeader = false;
            return strlen($data);
        }
        if ($this->_headerCount == 0) {
            $line = explode(" ", trim($data), 3);
            if (count($line) != 3) {
                $this->doError("Invalid response line returned from server: " . $data);
            }
            $code = intval($line[1]);
            if ($code === 100) {
                // Handle status 100 Continue
                $this->skipNextHeader = true;
                return strlen($data);
            }
            $this->_responseStatus = intval($line[1]);
        } else {
            $name = $value = '';
            $out = explode(": ", trim($data), 2);
            if (count($out) == 2) {
                $name = $out[0];
                $value = $out[1];
            }

            if (strlen($name)) {
                if ("Set-Cookie" == $name) {
                    if (!isset($this->_responseHeaders[$name])) {
                        $this->_responseHeaders[$name] = [];
                    }
                    $this->_responseHeaders[$name][] = $value;
                } else {
                    $this->_responseHeaders[$name] = $value;
                }
            }
        }
        $this->_headerCount++;

        return strlen($data);
    }
}
