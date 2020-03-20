<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Paazl\CheckoutWidget\Model\Carrier\Paazlshipping;
use Paazl\CheckoutWidget\Model\System\Config\Source\ApiMode;

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
     * @param null|Store|int|string $store
     *
     * @return mixed
     */
    public function getApiKey($store = null)
    {
        return $this->getValue(self::API_CONFIG_PATH . '/api_key', $store);
    }

    /**
     * @param string                $key
     * @param null|Store|int|string $store
     *
     * @return mixed
     */
    public function getValue($key, $store = null)
    {
        return $this->scopeConfig->getValue($key, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get module version from config
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->getValue(self::API_CONFIG_PATH . '/version');
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return mixed
     */
    public function getApiSecret($store = null)
    {
        return $this->getValue(self::API_CONFIG_PATH . '/api_secret', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return mixed
     */
    public function getReferencePrefix($store = null)
    {
        return $this->getValue(self::API_CONFIG_PATH . '/reference_prefix', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return bool
     */
    public function isProductionApiMode($store = null)
    {
        return $this->getApiMode($store) == ApiMode::MODE_PRODUCTION;
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return int
     */
    public function getApiMode($store = null)
    {
        $mode = $this->getValue(self::API_CONFIG_PATH . '/api_modus', $store);
        if ($mode != ApiMode::MODE_PRODUCTION) {
            $mode = ApiMode::MODE_STAGING;
        }

        return (int)$mode;
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return int
     */
    public function getApiTimeout($store = null)
    {
        $result = (int)$this->getValue(self::API_CONFIG_PATH . '/api_timeout', $store);
        if ($result <= 0) {
            $result = 300;
        }

        return $result;
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return mixed
     */
    public function getDefaultCountry($store = null)
    {
        return $this->getValue(self::API_CONFIG_PATH . '/default_country', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return mixed
     */
    public function getDefaultPostcode($store = null)
    {
        return $this->getValue(self::API_CONFIG_PATH . '/default_postcode', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return array
     */
    public function getAvailableTabs($store = null)
    {
        return explode(',', $this->getValue(self::API_CONFIG_PATH . '/widget_tabs', $store));
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return bool
     */
    public function getNominatedDateEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::API_CONFIG_PATH . '/widget_nominated_date',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return mixed
     */
    public function getDefaultTab($store = null)
    {
        return $this->getValue(self::API_CONFIG_PATH . '/widget_default_tab', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return int
     */
    public function getShippingOptionsLimit($store = null)
    {
        return (int)$this->getValue(self::API_CONFIG_PATH . '/widget_shipping_options_limit', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return int
     */
    public function getPickupLocationsPageLimit($store = null)
    {
        return (int)$this->getValue(self::API_CONFIG_PATH . '/widget_pickup_locations_page_limit', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return int
     */
    public function getPickupLocationsLimit($store = null)
    {
        return (int)$this->getValue(self::API_CONFIG_PATH . '/widget_pickup_locations_limit', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return int
     */
    public function getInitialPickupLocations($store = null)
    {
        return (int)$this->getValue(self::API_CONFIG_PATH . '/widget_initial_pickup_locations', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return string|null
     */
    public function getCustomCss($store = null)
    {
        if ($this->getWidgetTheme($store) == 'CUSTOM') {
            return $this->getValue(self::API_CONFIG_PATH . '/custom_css', $store);
        }
        return null;
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return string
     */
    public function getWidgetTheme($store = null)
    {
        return $this->getValue(self::API_CONFIG_PATH . '/widget_theme', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return mixed
     */
    public function getDebugEnabled($store = null)
    {
        return $this->getValue(self::API_CONFIG_PATH . '/debug', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return mixed
     */
    public function isHideOtherShippingMethods($store = null)
    {
        return (bool)$this->getValue(self::API_CONFIG_PATH . '/hide_method_select', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return bool
     */
    public function isCarrierActive($store = null)
    {
        return $this->isEnabled($store) &&
            in_array($this->getValue('carriers/' . Paazlshipping::CODE . '/active', $store), [1, 'true']);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return mixed
     */
    public function isEnabled($store = null)
    {
        return $this->getValue(self::API_CONFIG_PATH . '/active', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return mixed
     */
    public function housenumberOnSecondStreet($store = null)
    {
        return (bool)$this->getValue(self::API_CONFIG_PATH . '/housenumber_second_street', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return mixed
     */
    public function housenumberExtensionOnThirdStreet($store = null)
    {
        return (bool)$this->getValue(self::API_CONFIG_PATH . '/housenumber_extension_third_street', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return mixed|null
     */
    public function getCustomDescription($store = null)
    {
        if (!$this->getValue(self::API_CONFIG_PATH . '/use_custom_description', $store)) {
            return null;
        }

        return $this->getValue(self::API_CONFIG_PATH . '/custom_description', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return mixed
     */
    public function getProductAttributeWidth($store = null)
    {
        return $this->getValue(self::API_CONFIG_PATH . '/width_attribute', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return mixed
     */
    public function getProductAttributeLength($store = null)
    {
        return $this->getValue(self::API_CONFIG_PATH . '/length_attribute', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return mixed
     */
    public function getProductAttributeHeight($store = null)
    {
        return $this->getValue(self::API_CONFIG_PATH . '/height_attribute', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return int|null
     */
    public function getProductAttributeNumberOfProcessingDays($store = null)
    {
        return $this->getValue(self::API_CONFIG_PATH . '/number_of_processing_days_attribute', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return mixed
     */
    public function getProductAttributeCountyOfManufacture($store = null)
    {
        return $this->getValue(self::API_CONFIG_PATH . '/country_of_manufacture_attribute', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return mixed
     */
    public function getProductStaticCountyOfManufacture($store = null)
    {
        return $this->getValue(self::API_CONFIG_PATH . '/country_of_manufacture_static', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return mixed
     */
    public function getProductAttributeHsTariffCode($store = null)
    {
        return $this->getValue(self::API_CONFIG_PATH . '/hs_tariff_code_attribute', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return mixed
     */
    public function getProductStaticHsTariffCode($store = null)
    {
        return $this->getValue(self::API_CONFIG_PATH . '/hs_tariff_code_static', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return mixed
     */
    public function getGoogleMapKey($store = null)
    {
        if ($this->getValue(self::API_CONFIG_PATH . '/googlemaps', $store)) {
            return $this->getValue(self::API_CONFIG_PATH . '/googlemaps_key', $store);
        }
        return null;
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return mixed
     */
    public function getProductAttributeDeliveryMatrix($store = null)
    {
        return $this->getValue(self::API_CONFIG_PATH . '/delivery_matrix_attribute', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return mixed
     */
    public function getCarrierTitle($store = null)
    {
        return $this->getValue(self::API_CONFIG_PATH . '/title', $store);
    }

    /**
     * @param null|Store|int|string $store
     *
     * @return bool
     */
    public function saveShippingInformationInstantly($store = null)
    {
        return !!$this->getValue(self::API_CONFIG_PATH . '/onestep_checkout_used', $store);
    }
}
