<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Block\Checkout;

use Magento\Framework\View\Element\Template;
use Paazl\CheckoutWidget\Model\Api\UrlProvider;
use Paazl\CheckoutWidget\Model\Api\UrlProviderFactory;
use Paazl\CheckoutWidget\Model\Checkout\WidgetConfigProvider;
use Paazl\CheckoutWidget\Model\Config;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Widget
 *
 * @package Paazl\CheckoutWidget\Block\Checkout
 */
class Widget extends Template
{

    /**
     * @var Config
     */
    private $scopeConfig;

    /**
     * @var WidgetConfigProvider
     */
    private $widgetConfigProvider;

    /**
     * @var UrlProvider
     */
    private $urlProvider;

    /**
     * Widget constructor.
     *
     * @param Context              $context
     * @param Config               $scopeConfig
     * @param UrlProviderFactory   $urlProviderFactory
     * @param WidgetConfigProvider $widgetConfigProvider
     * @param array                $data
     */
    public function __construct(
        Context $context,
        Config $scopeConfig,
        UrlProviderFactory $urlProviderFactory,
        WidgetConfigProvider $widgetConfigProvider,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->widgetConfigProvider = $widgetConfigProvider;
        $this->urlProvider = $urlProviderFactory->create();
        parent::__construct($context, $data);
    }

    // ---

    /**
     * @return mixed
     */
    public function getCustomCss()
    {
        return $this->scopeConfig->getCustomCss();
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isEnabled();
    }

    /**
     * @return boolean
     */
    public function getGoogleMapKey()
    {
        return $this->scopeConfig->getGoogleMapKey();
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->isEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return string
     */
    public function getApiBaseUrl()
    {
        return $this->urlProvider->getBaseUrl();
    }
}
