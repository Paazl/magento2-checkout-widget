<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Paazl\CheckoutWidget\Model\Api\UrlProvider;
use Paazl\CheckoutWidget\Model\Api\UrlProviderFactory;
use Paazl\CheckoutWidget\Model\Carrier\Paazlshipping;
use Paazl\CheckoutWidget\Model\Config;

/**
 * Class PaazlConfigProvider
 *
 * @package Paazl\CheckoutWidget\Model\Checkout
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
     * PaazlConfigProvider constructor.
     *
     * @param Config               $config
     * @param UrlProviderFactory   $urlProviderFactory
     * @param WidgetConfigProvider $widgetConfigProvider
     */
    public function __construct(
        Config $config,
        UrlProviderFactory $urlProviderFactory,
        WidgetConfigProvider $widgetConfigProvider
    ) {
        $this->config = $config;
        $this->urlProvider = $urlProviderFactory->create();
        $this->widgetConfigProvider = $widgetConfigProvider;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        if (!$this->config->isEnabled()) {
            return $config;
        }

        $config['hideOtherMethods'] = $this->config->isHideOtherShippingMethods();
        $config['carrierCode'] = Paazlshipping::CODE;
        $config['methodCode'] = Paazlshipping::CODE;
        $config['baseApiUrl'] = $this->urlProvider->getBaseUrl();
        $config['checkoutApiUrl'] = $this->urlProvider->getCheckoutUrl();
        $config['saveShippingInfoInstantly'] = $this->config->saveShippingInformationInstantly();
        $config['googleMapKey'] = $this->config->getGoogleMapKey();
        $config['widgetConfig'] = $this->widgetConfigProvider->getConfig();
        $config['mode'] = $this->config->isProductionApiMode() ? 'live' : 'test';
        $config['showOnFirstLoad'] = $this->config->showWidgetOnFirstLoad();
        $config['useLocalCopyOfWidgetJs'] = $this->config->isUseLocalCopyOfWidgetJs();

        if (empty($config['widgetConfig']['token'])) {
            // We were unable to obtain a token - enabling other methods if they're available
            $config['hideOtherMethods'] = false;
        }

        return [
            'paazlshipping' => $config
        ];
    }
}
