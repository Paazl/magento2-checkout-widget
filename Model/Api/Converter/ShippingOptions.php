<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Api\Converter;

use Magento\Framework\Serialize\Serializer\Json;
use Paazl\CheckoutWidget\Helper\General;

class ShippingOptions
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @var General
     */
    private $generalHelper;

    /**
     * ShippingOptions constructor.
     *
     * @param Json    $json
     * @param General $generalHelper
     */
    public function __construct(
        Json $json,
        General $generalHelper
    ) {
        $this->json = $json;
        $this->generalHelper = $generalHelper;
    }

    /**
     * @param string|null $response
     * @return array|null
     */
    public function convert($response)
    {
        try {
            return $this->json->unserialize($response);
        } catch (\InvalidArgumentException $e) {
            $this->generalHelper->addTolog('exception', $e->getMessage());
        }

        return null;
    }
}
