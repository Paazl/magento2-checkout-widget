<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Logger;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

class PaazlHandler extends Base
{
    protected $loggerType = Logger::DEBUG;
    protected $fileName = '/var/log/paazl.log';
}
