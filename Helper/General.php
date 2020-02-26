<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Paazl\CheckoutWidget\Model\Config;
use Paazl\CheckoutWidget\Logger\PaazlLogger;

/**
 * Class General
 *
 * @package Paazl\CheckoutWidget\Helper
 */
class General extends AbstractHelper
{

    /**
     * @var string
     */
    const MODULE_CODE = 'Paazl_CheckoutWidget';

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var ProductMetadataInterface
     */
    private $metadata;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var PaazlLogger
     */
    private $logger;

    /**
     * General constructor.
     *
     * @param Context                  $context
     * @param ModuleListInterface      $moduleList
     * @param ProductMetadataInterface $metadata
     * @param Config                   $config
     * @param PaazlLogger              $logger
     */
    public function __construct(
        Context $context,
        ModuleListInterface $moduleList,
        ProductMetadataInterface $metadata,
        Config $config,
        PaazlLogger $logger
    ) {
        $this->moduleList = $moduleList;
        $this->metadata = $metadata;
        $this->config = $config;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Returns current version of the extension
     *
     * @return mixed
     */
    public function getExtensionVersion()
    {
        $moduleInfo = $this->moduleList->getOne(self::MODULE_CODE);
        return $moduleInfo['setup_version'];
    }

    /**
     * Returns current version of Magento
     *
     * @return string
     */
    public function getMagentoVersion()
    {
        return $this->metadata->getVersion();
    }

    /**
     * @param string $type
     * @param mixed $data
     */
    public function addTolog($type, $data)
    {
        if ($this->config->getDebugEnabled()) {
            $this->logger->add($type, $data);
        }
    }
}
