<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Block\Checkout;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Paazl\CheckoutWidget\Model\Api\UrlProvider;
use Paazl\CheckoutWidget\Model\Api\UrlProviderFactory;
use Paazl\CheckoutWidget\Model\Config;

class Widget extends Template
{

    /**
     * @var Config
     */
    private $scopeConfig;

    /**
     * @var UrlProvider
     */
    private $urlProvider;

    /**
     * Widget constructor.
     *
     * @param Context $context
     * @param Config $scopeConfig
     * @param UrlProviderFactory $urlProviderFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $scopeConfig,
        UrlProviderFactory $urlProviderFactory,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->urlProvider = $urlProviderFactory->create();
        parent::__construct($context, $data);
    }

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
    public function getGoogleMapKey()
    {
        return $this->scopeConfig->getGoogleMapKey();
    }

    /**
     * @return string
     */
    public function getApiBaseUrl()
    {
        return $this->urlProvider->getBaseUrl();
    }

    /**
     * @return boolean
     */
    public function useLocal(): bool
    {
        return $this->scopeConfig->isUseLocalCopyOfWidgetJs();
    }

    /**
     * @return string
     */
    public function getLocalResourceUrl(): string
    {
        $cssFile = 'paazl-checkout.min.css';
        $url = $this->getViewFileUrl('Paazl_CheckoutWidget::css/' . $cssFile);
        return str_replace($cssFile, '', $url);
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
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isEnabled();
    }
}
