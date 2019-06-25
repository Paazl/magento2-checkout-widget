<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Logger;

use Monolog\Logger;

/**
 * Class PaazlLogger
 *
 * @package Paazl\CheckoutWidget\Logger
 */
class PaazlLogger extends Logger
{

    /**
     * @param $type
     * @param $data
     */
    public function add($type, $data)
    {
        if (is_array($data)) {
            $this->addInfo($type . ': ' . json_encode($data));
        } elseif (is_object($data)) {
            $this->addInfo($type . ': ' . json_encode($data));
        } else {
            $this->addInfo($type . ': ' . $data);
        }
    }
}
