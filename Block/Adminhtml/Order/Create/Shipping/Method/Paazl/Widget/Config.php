<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Block\Adminhtml\Order\Create\Shipping\Method\Paazl\Widget;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Serialize\SerializerInterface;
use Paazl\CheckoutWidget\Model\CompositeConfigProvider;

/**
 * PaazlShipping method block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Config extends Template
{
    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'Paazl_CheckoutWidget::order/create/shipping/method/paazl/widget/config.phtml';

    /**
     * @var CompositeConfigProvider
     */
    protected $configProvider;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param CompositeConfigProvider $configProvider
     * @param SerializerInterface $serializer
     * @param Context $context
     * @param array $data
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        CompositeConfigProvider $configProvider,
        SerializerInterface $serializer,
        Context $context,
        array $data = []
    ) {
        $this->configProvider = $configProvider;
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    /**
     * Retrieves order configuration
     *
     * @return array
     */
    public function getWidgetConfig()
    {
        return $this->configProvider->getConfig();
    }

    /**
     * Retrieves serialized order config.
     *
     * @return bool|string
     */
    public function getSerializedWidgetConfig()
    {
        return  $this->serializer->serialize($this->getWidgetConfig());
    }
}
