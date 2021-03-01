<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Paazl\CheckoutWidget\Logger\PaazlLogger;
use Paazl\CheckoutWidget\Model\ExtInfoHandler;
use Paazl\CheckoutWidget\Model\Config;
use Magento\Framework\App\State as AppState;
use Magento\Framework\App\Area;
use Paazl\CheckoutWidget\Model\TokenRetriever;
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
     * @var PaazlLogger
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
     * @var TokenRetriever
     */
    private $tokenRetriever;

    /**
     * Paazlshipping constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory         $rateErrorFactory
     * @param LoggerInterface      $logger
     * @param AppState             $appState
     * @param ResultFactory        $rateResultFactory
     * @param MethodFactory        $rateMethodFactory
     * @param Config               $config
     * @param ExtInfoHandler       $extInfoHandler
     * @param TokenRetriever       $tokenRetriever
     * @param PaazlLogger          $paazlLogger
     * @param array                $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        AppState $appState,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        Config $config,
        ExtInfoHandler $extInfoHandler,
        TokenRetriever $tokenRetriever,
        PaazlLogger $paazlLogger,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->logger = $paazlLogger;

        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->extInfoHandler = $extInfoHandler;
        $this->config = $config;
        $this->appState = $appState;
        $this->tokenRetriever = $tokenRetriever;
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
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();
        $shippingPrice = 0;
        $method = $this->rateMethodFactory->create();

        /**
         * Displayed as shipping method under Carrier
         */
        $method->setMethod($this->getCarrierCode());
        $method->setMethodTitle($this->getConfigData('name'));

        // Recalculate shipping price
        $quote = $this->extractQuote($request);
        if (!$quote || (!$quote->getId())) {
            /*
             * No quote. Can happen when 1st product was added to quote.
             * Return method's "placeholder", we'll obtain a token at a later stage.
             */
            $method->setCarrier($this->getCarrierCode());
            $method->setCarrierTitle($this->getConfigData('title'));
            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);
            $result->append($method);
            return $result;
        }

        try {
            $this->tokenRetriever->retrieveByQuote($quote);

            $info = $this->extInfoHandler->getInfoFromQuote($quote);

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
        } catch (LocalizedException $e) {
            $this->logger->add('exception', $e->getLogMessage());
        }

        return null;
    }

    /**
     * @param RateRequest $request
     *
     * @return \Magento\Quote\Model\Quote|null
     */
    private function extractQuote(RateRequest $request)
    {
        $quote = null;
        $items = $request->getAllItems();
        $current = current($items);
        if ($current instanceof Item) {
            $quote = $current->getQuote();
        }

        return $quote;
    }
}
