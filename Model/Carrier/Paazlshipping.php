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
use Paazl\CheckoutWidget\Api\CheckoutSelection\RepositoryInterface as CheckoutSelectionRepository;
use Paazl\CheckoutWidget\Logger\PaazlLogger;
use Paazl\CheckoutWidget\Model\Api\Converter\ShippingOptions;
use Paazl\CheckoutWidget\Model\Api\PaazlApiFactory;
use Paazl\CheckoutWidget\Model\Checkout\WidgetConfigProvider;
use Paazl\CheckoutWidget\Model\ExtInfoHandler;
use Paazl\CheckoutWidget\Model\Config;
use Magento\Framework\App\State as AppState;
use Magento\Framework\App\Area;
use Paazl\CheckoutWidget\Model\Api\Field\DeliveryType;
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
    public const CODE = 'paazlshipping';

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
     * @var PaazlApiFactory
     */
    private $apiFactory;

    /**
     * @var ShippingOptions
     */
    private $shippingOptionsConverter;

    /**
     * @var WidgetConfigProvider
     */
    private $configProvider;

    private CheckoutSelectionRepository $checkoutSelectionRepository;

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
        PaazlApiFactory $apiFactory,
        ShippingOptions $shippingOptionsConverter,
        WidgetConfigProvider $configProvider,
        CheckoutSelectionRepository $checkoutSelectionRepository,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->logger = $paazlLogger;
        $this->apiFactory = $apiFactory;
        $this->shippingOptionsConverter = $shippingOptionsConverter;
        $this->configProvider = $configProvider;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->extInfoHandler = $extInfoHandler;
        $this->config = $config;
        $this->appState = $appState;
        $this->tokenRetriever = $tokenRetriever;
        $this->checkoutSelectionRepository = $checkoutSelectionRepository;
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
            $customerCountryId = $quote->getShippingAddress()->getCountryId();
            $customerPostcode  = $quote->getShippingAddress()->getPostcode();
            $countryId = $customerCountryId ?: $this->configProvider->getDefaultCountry();
            $postcode  = $customerPostcode  ?: $this->configProvider->getDefaultPostcode();
            if ($countryId && $postcode) {
                $api = $this->apiFactory->create($quote->getStoreId());
                $config = $this->configProvider->setQuote($quote)->getConfig();
                $shippingOptions = $this->shippingOptionsConverter->convert(
                    $api->getShippingOptions($config)
                );
                // Only persist a checkout selection once the customer has
                // entered both country and postcode themselves. Otherwise the
                // row would reflect store-config defaults, not consumer behaviour.
                if ($customerCountryId
                    && $customerPostcode
                    && $this->config->saveCheckoutSelections($quote->getStoreId())
                ) {
                    try {
                        $quoteId = (int)$quote->getId();
                        $checkoutSelection = $this->checkoutSelectionRepository->getByQuoteId($quoteId);
                        if ($checkoutSelection === null) {
                            $checkoutSelection = $this->checkoutSelectionRepository->create();
                            $checkoutSelection->setQuoteId($quoteId);
                        }
                        $checkoutSelection->setExtShippingInfo($shippingOptions);
                        $this->checkoutSelectionRepository->save($checkoutSelection);
                    } catch (\Exception $e) {
                        // do nothing
                    }
                }
                if (isset($shippingOptions['shippingOptions'][0])) {
                    $firstOption = $shippingOptions['shippingOptions'][0];
                    $shippingPrice = $firstOption['rate'];
                    $method->setMethodTitle($firstOption['name']);
                } else {
                    if (!$this->config->getAllowWithoutShippingOption($quote->getStoreId())) {
                        return false;
                    }
                }
            }

            $this->tokenRetriever->retrieveByQuote($quote);

            $info = $this->extInfoHandler->getInfoFromQuote($quote);

            // Use selected shipping option if it still exists in the current API response
            if ($info && $info->getType() && $quote->getShippingAddress()->getShippingMethod()) {
                if ($info->getType() === DeliveryType::PICKUP) {
                    // Pickup locations are not in shippingOptions; trust the stored info
                    $shippingPrice = $info->getPrice();
                    if ($info->getOptionTitle()) {
                        $method->setMethodTitle($info->getOptionTitle());
                    }
                } else {
                    // For home delivery, validate the selected option against current API response
                    $selectedIdentifier = $info->getIdenfifier();
                    if ($selectedIdentifier && isset($shippingOptions['shippingOptions'])) {
                        foreach ($shippingOptions['shippingOptions'] as $option) {
                            if (isset($option['identifier']) && $option['identifier'] === $selectedIdentifier) {
                                $shippingPrice = (float)$option['rate'];
                                if ($info->getOptionTitle()) {
                                    $method->setMethodTitle($info->getOptionTitle());
                                }
                                break;
                            }
                        }
                    }
                }
            }

            $shippingPrice = $this->normalizeShippingPrice($shippingPrice, $quote);

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
     * When Paazl returns rates in the storefront display currency (e.g., a DKK-configured
     * matrix on a DKK storefront) Magento's core still multiplies Rate\Method::setPrice()
     * by the base-to-quote rate when storing shipping_amount. Pre-divide by the same rate
     * so the multiplication cancels out and the displayed amount equals the Paazl rate.
     *
     * @param float $shippingPrice
     * @param \Magento\Quote\Model\Quote $quote
     * @return float
     */
    private function normalizeShippingPrice($shippingPrice, $quote)
    {
        if (!$this->config->isShippingPriceInDisplayCurrency($quote->getStoreId())) {
            return (float)$shippingPrice;
        }

        $rate = (float)$quote->getBaseToQuoteRate();
        if ($rate <= 0) {
            return (float)$shippingPrice;
        }

        return (float)$shippingPrice / $rate;
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
