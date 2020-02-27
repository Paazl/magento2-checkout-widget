<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Admin\Order\Create;

use Magento\Backend\Model\Session\Quote as SessionQuote;
use Magento\Checkout\Model\ConfigProviderInterface;
use Paazl\CheckoutWidget\Model\Api\UrlProvider;
use Paazl\CheckoutWidget\Model\Api\UrlProviderFactory;
use Paazl\CheckoutWidget\Model\Carrier\Paazlshipping;
use Paazl\CheckoutWidget\Model\Config;

/**
 * Class PaazlConfigProvider
 */
class PaazlConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var UrlProvider
     */
    private $urlProvider;

    /**
     * @var WidgetConfigProvider
     */
    private $widgetConfigProvider;

    /**
     * @var SessionQuote
     */
    private $sessionQuote;

    /**
     * PaazlConfigProvider constructor.
     *
     * @param SessionQuote         $sessionQuote
     * @param Config               $config
     * @param UrlProviderFactory   $urlProviderFactory
     * @param WidgetConfigProvider $widgetConfigProvider
     */
    public function __construct(
        SessionQuote $sessionQuote,
        Config $config,
        UrlProviderFactory $urlProviderFactory,
        WidgetConfigProvider $widgetConfigProvider
    ) {
        $this->config = $config;
        $this->urlProvider = $urlProviderFactory->create($sessionQuote->getQuote()->getStoreId());
        $this->widgetConfigProvider = $widgetConfigProvider;
        $this->sessionQuote = $sessionQuote;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        $storeId = $this->sessionQuote->getQuote()->getStoreId();
        if (!$this->config->isEnabled($storeId) || !$this->sessionQuote->getQuote()->getId()) {
            return $config;
        }

        $config['hideOtherMethods'] = $this->config->isHideOtherShippingMethods($storeId);
        $config['carrierCode'] = Paazlshipping::CODE;
        $config['methodCode'] = Paazlshipping::CODE;
        $config['baseApiUrl'] = $this->urlProvider->getBaseUrl();
        $config['googleMapKey'] = $this->config->getGoogleMapKey($storeId);
        $config['widgetConfig'] = $this->widgetConfigProvider->getConfig();
        $config['mode'] = $this->config->isProductionApiMode($storeId) ? 'live' : 'test';

        if (empty($config['widgetConfig']['token'])) {
            // We were unable to obtain a token - enabling other methods if they're available
            $config['hideOtherMethods'] = false;
        }

        return [
            'paazlshipping' => $config
        ];
    }
}
