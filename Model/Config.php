<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Paazl\CheckoutWidget\Model\Carrier\Paazlshipping;

/**
 * Class Config
 *
 * @package Paazl\CheckoutWidget\Model
 */
class Config
{
    /**#@+
     * Constants
     */
    const API_CONFIG_PATH = 'carriers/paazlshipping';
    const MIN_NUMBER_OF_PROCESSING_DAYS = 1;
    const MAX_NUMBER_OF_PROCESSING_DAYS = 99;
    /**#@-*/

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->getValue(self::API_CONFIG_PATH . '/api_key');
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getValue($key)
    {
        return $this->scopeConfig->getValue($key, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getApiSecret()
    {
        return $this->getValue(self::API_CONFIG_PATH . '/api_secret');
    }

    /**
     * @return mixed
     */
    public function getReferencePrefix()
    {
        return $this->getValue(self::API_CONFIG_PATH . '/reference_prefix');
    }

    /**
     * @return bool
     */
    public function isProductionApiMode()
    {
        return $this->getApiMode() == 1;
    }

    /**
     * @return mixed
     */
    public function getApiMode()
    {
        return $this->getValue(self::API_CONFIG_PATH . '/api_modus');
    }

    /**
     * @return int
     */
    public function getApiTimeout()
    {
        $result = (int)$this->getValue(self::API_CONFIG_PATH . '/api_timeout');
        if ($result <= 0) {
            $result = 300;
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function getDefaultCountry()
    {
        return $this->getValue(self::API_CONFIG_PATH . '/default_country');
    }

    /**
     * @return mixed
     */
    public function getDefaultPostcode()
    {
        return $this->getValue(self::API_CONFIG_PATH . '/default_postcode');
    }

    /**
     * @return array
     */
    public function getAvailableTabs()
    {
        return explode(',', $this->getValue(self::API_CONFIG_PATH . '/widget_tabs'));
    }

    /**
     * @return string
     */
    public function getNominatedDateEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::API_CONFIG_PATH . '/widget_nominated_date');
    }

    /**
     * @return mixed
     */
    public function getDefaultTab()
    {
        return $this->getValue(self::API_CONFIG_PATH . '/widget_default_tab');
    }

    /**
     * @return int
     */
    public function getShippingOptionsLimit()
    {
        return (int)$this->getValue(self::API_CONFIG_PATH . '/widget_shipping_options_limit');
    }

    /**
     * @return int
     */
    public function getPickupLocationsPageLimit()
    {
        return (int)$this->getValue(self::API_CONFIG_PATH . '/widget_pickup_locations_page_limit');
    }

    /**
     * @return int
     */
    public function getPickupLocationsLimit()
    {
        return (int)$this->getValue(self::API_CONFIG_PATH . '/widget_pickup_locations_limit');
    }

    /**
     * @return int
     */
    public function getInitialPickupLocations()
    {
        return (int)$this->getValue(self::API_CONFIG_PATH . '/widget_initial_pickup_locations');
    }

    /**
     * @return string
     */
    public function getCustomCss()
    {
        if ($this->getWidgetTheme() == 'CUSTOM') {
            return $this->getValue(self::API_CONFIG_PATH . '/custom_css');
        }
        return null;
    }

    /**
     * @return string
     */
    public function getWidgetTheme()
    {
        return $this->getValue(self::API_CONFIG_PATH . '/widget_theme');
    }

    /**
     * @return mixed
     */
    public function getDebugEnabled()
    {
        return $this->getValue(self::API_CONFIG_PATH . '/debug');
    }

    /**
     * @return mixed
     */
    public function isHideOtherShippingMethods()
    {
        return (bool)$this->getValue(self::API_CONFIG_PATH . '/hide_method_select');
    }

    /**
     * @return bool
     */
    public function isCarrierActive()
    {
        return $this->isEnabled() &&
            in_array($this->getValue('carriers/' . Paazlshipping::CODE . '/active'), [1, 'true']);
    }

    /**
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->getValue(self::API_CONFIG_PATH . '/active');
    }

    /**
     * @return mixed
     */
    public function housenumberOnSecondStreet()
    {
        return (bool)$this->getValue(self::API_CONFIG_PATH . '/housenumber_second_street');
    }

    /**
     * @return mixed
     */
    public function housenumberAdditionOnThridStreet()
    {
        return (bool)$this->getValue(self::API_CONFIG_PATH . '/housenumber_addition_third_street');
    }

    /**
     * @return mixed|null
     */
    public function getCustomDescription()
    {
        if (!$this->getValue(self::API_CONFIG_PATH . '/use_custom_description')) {
            return null;
        }

        return $this->getValue(self::API_CONFIG_PATH . '/custom_description');
    }

    /**
     * @return mixed
     */
    public function getProductAttributeWidth()
    {
        return $this->getValue(self::API_CONFIG_PATH . '/width_attribute');
    }

    /**
     * @return mixed
     */
    public function getProductAttributeLength()
    {
        return $this->getValue(self::API_CONFIG_PATH . '/length_attribute');
    }

    /**
     * @return mixed
     */
    public function getProductAttributeHeight()
    {
        return $this->getValue(self::API_CONFIG_PATH . '/height_attribute');
    }

    /**
     * @return int|null
     */
    public function getProductAttributeNumberOfProcessingDays()
    {
        return $this->getValue(self::API_CONFIG_PATH . '/number_of_processing_days_attribute');
    }

    /**
     * @return mixed
     */
    public function getProductAttributeCountyOfManufacture()
    {
        return $this->getValue(self::API_CONFIG_PATH . '/country_of_manufacture_attribute');
    }

    /**
     * @return mixed
     */
    public function getProductStaticCountyOfManufacture()
    {
        return $this->getValue(self::API_CONFIG_PATH . '/country_of_manufacture_static');
    }

    /**
     * @return mixed
     */
    public function getProductAttributeHsTariffCode()
    {
        return $this->getValue(self::API_CONFIG_PATH . '/hs_tariff_code_attribute');
    }

    /**
     * @return mixed
     */
    public function getProductStaticHsTariffCode()
    {
        return $this->getValue(self::API_CONFIG_PATH . '/hs_tariff_code_static');
    }

    /**
     * @return mixed
     */
    public function getGoogleMapKey()
    {
        if ($this->getValue(self::API_CONFIG_PATH . '/googlemaps')) {
            return $this->getValue(self::API_CONFIG_PATH . '/googlemaps_key');
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getProductAttributeDeliveryMatrix()
    {
        return $this->getValue(self::API_CONFIG_PATH . '/delivery_matrix_attribute');
    }

    /**
     * @return mixed
     */
    public function getCarrierTitle()
    {
        return $this->getValue(self::API_CONFIG_PATH . '/title');
    }
}