<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Logger;

use Magento\Framework\Serialize\Serializer\Json;
use Monolog\Logger as MonologLogger;

class PaazlLogger
{
    private MonologLogger $logger;
    private Json $json;

    public function __construct(
        MonologLogger $logger,
        Json $json
    ) {
        $this->logger = $logger;
        $this->json = $json;
    }

    public function add(string $type, $data): void
    {
        if (is_array($data) || is_object($data)) {
            $this->logger->info( $type . ': ' . $this->json->serialize($data));
        } else {
            $this->logger->info( $type . ': ' . $data);
        }
    }
}
