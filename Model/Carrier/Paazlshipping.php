<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Paazl\CheckoutWidget\Model\ExtInfoHandler;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Paazl\CheckoutWidget\Model\Config;
use Magento\Framework\App\State as AppState;
use Magento\Framework\App\Area;
use Psr\Log\LoggerInterface;

/**
 * Class Paazlshipping
 *
 * @package Paazl\CheckoutWidget\Model\Carrier
 */
class Paazlshipping extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    const CODE = 'paazlshipping';

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = 'paazlshipping';

    /**
     * Whether this carrier has fixed rates calculation
     *
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var ResultFactory
     */
    private $rateResultFactory;

    /**
     * @var MethodFactory
     */
    private $rateMethodFactory;

    /**
     * @var CheckoutHelper
     */
    private $checkoutHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ExtInfoHandler
     */
    private $extInfoHandler;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * Paazlshipping constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory         $rateErrorFactory
     * @param LoggerInterface      $logger
     * @param AppState             $appState
     * @param ResultFactory        $rateResultFactory
     * @param MethodFactory        $rateMethodFactory
     * @param CheckoutHelper       $checkoutHelper
     * @param Config               $config
     * @param ExtInfoHandler       $extInfoHandler
     * @param array                $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        AppState $appState,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        CheckoutHelper $checkoutHelper,
        Config $config,
        ExtInfoHandler $extInfoHandler,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->checkoutHelper = $checkoutHelper;
        $this->logger = $logger;

        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->extInfoHandler = $extInfoHandler;
        $this->config = $config;
        $this->appState = $appState;
    }

    /**
     * @return string
     */
    public function getCarrierCode()
    {
        return $this->_code;
    }

    /**
     * {@inheritDoc}
     */
    public function isActive()
    {
        $areaCode = $this->appState->getAreaCode();
        if ($areaCode === Area::AREA_ADMIN ||
            $areaCode === Area::AREA_ADMINHTML) {
            return false;
        }

        return parent::isActive() && $this->config->isEnabled();
    }

    /**
     * Generates list of allowed carrier`s shipping methods
     * Displays on cart price rules page
     *
     * @return array
     * @api
     */
    public function getAllowedMethods()
    {
        return [$this->getCarrierCode() => __($this->getConfigData('name'))];
    }

    /**
     * @param RateRequest $request
     *
     * @return bool|DataObject|\Magento\Shipping\Model\Rate\Result|null
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->isActive()) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();
        $shippingPrice = $this->getConfigData('price');
        $method = $this->rateMethodFactory->create();

        /**
         * Displayed as shipping method under Carrier
         */
        $method->setMethod($this->getCarrierCode());
        $method->setMethodTitle($this->getConfigData('name'));

        // Recalculate shipping price
        $info = $this->extInfoHandler->getInfoFromQuote($quote = $this->checkoutHelper->getQuote());

        if ($info && $info->getType()) {
            $shippingPrice = $info->getPrice();
            if ($info->getOptionTitle()) {
                $method->setMethodTitle($info->getOptionTitle());
            }
        }

        $method->setCarrier($this->getCarrierCode());
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);
        $result->append($method);
        return $result;
    }
}
