<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Api;

use Magento\Framework\ObjectManagerInterface;
use Paazl\CheckoutWidget\Model\Config;

/**
 * Store-aware factory for UrlProvider
 */
class UrlProviderFactory
{

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * UrlProviderFactory constructor.
     *
     * @param ObjectManagerInterface             $objectManager
     * @param Config $config
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Config $config
    ) {
        $this->objectManager = $objectManager;
        $this->config = $config;
    }

    /**
     * @param null|int|string $storeId
     *
     * @return UrlProvider
     */
    public function create($storeId = null): UrlProvider
    {
        return $this->objectManager->create(UrlProvider::class, ['mode' => $this->config->getApiMode($storeId)]);
    }
}
