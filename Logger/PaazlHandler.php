<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Logger;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

/**
 * Class PaazlHandler
 *
 * @package Paazl\CheckoutWidget\Logger
 */
class PaazlHandler extends Base
{

    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;
    /**
     * @var string
     */
    protected $fileName = '/var/log/paazl.log';
}
