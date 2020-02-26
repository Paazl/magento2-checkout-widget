<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Api;

use Magento\Framework\ObjectManagerInterface;
use Paazl\CheckoutWidget\Model\Config;

/**
 * Store-aware factory for the API client.
 */
class PaazlApiFactory
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
     * PaazlApiFactory constructor.
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
     * @param null|int $storeId
     *
     * @return PaazlApi
     */
    public function create($storeId = null): PaazlApi
    {
        /** @var Configuration $configuration */
        $configuration = $this->objectManager->create(Configuration::class);
        $configuration
            ->setKey($this->config->getApiKey($storeId))
            ->setSecret($this->config->getApiSecret($storeId))
            ->setMode($this->config->getApiMode($storeId))
            ->setTimeout($this->config->getApiTimeout($storeId));

        return $this->objectManager->create(
            PaazlApi::class,
            [
                'configuration' => $configuration,
                'urlProvider'   => $this->objectManager->get(UrlProviderFactory::class)->create($storeId)
            ]
        );
    }
}
