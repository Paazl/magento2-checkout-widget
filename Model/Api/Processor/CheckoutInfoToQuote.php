<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Api\Processor;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Paazl\CheckoutWidget\Helper\General;
use Paazl\CheckoutWidget\Model\Api\ApiException;
use Paazl\CheckoutWidget\Model\Api\Builder\Reference;
use Paazl\CheckoutWidget\Model\Api\Converter\Checkout\ToShippingInfo;
use Paazl\CheckoutWidget\Model\Api\PaazlApi;
use Paazl\CheckoutWidget\Model\Api\PaazlApiFactory;
use Paazl\CheckoutWidget\Model\ExtInfoHandler;

/**
 * Class CheckoutInfoToQuote
 *
 * @package Paazl\CheckoutWidget\Model\Api\Processor
 */
class CheckoutInfoToQuote
{

    /**
     * @var PaazlApiFactory
     */
    private $paazlApiFactory;

    /**
     * @var Reference
     */
    private $referenceBuilder;

    /**
     * @var ToShippingInfo
     */
    private $shippingInfo;

    /**
     * @var ExtInfoHandler
     */
    private $extInfoHandler;

    /**
     * @var General
     */
    private $generalHelper;

    /**
     * CheckoutInfoToQuote constructor.
     *
     * @param PaazlApiFactory $paazlApiFactory
     * @param Reference       $referenceBuilder
     * @param ToShippingInfo  $shippingInfo
     * @param ExtInfoHandler  $extInfoHandler
     * @param General         $generalHelper
     */
    public function __construct(
        PaazlApiFactory $paazlApiFactory,
        Reference $referenceBuilder,
        ToShippingInfo $shippingInfo,
        ExtInfoHandler $extInfoHandler,
        General $generalHelper
    ) {
        $this->paazlApiFactory = $paazlApiFactory;
        $this->referenceBuilder = $referenceBuilder;
        $this->shippingInfo = $shippingInfo;
        $this->extInfoHandler = $extInfoHandler;
        $this->generalHelper = $generalHelper;
    }

    /**
     * @param Quote $quote
     *
     * @throws LocalizedException
     */
    public function process(Quote $quote)
    {
        try {
            // Getting the checkout information from Paazl
            /** @var PaazlApi $paazlApi */
            $paazlApi = $this->paazlApiFactory->create($quote->getStoreId());
            $result = $paazlApi->fetchCheckoutData($this->referenceBuilder->getQuoteReference($quote));

            // Converting response to extInfo
            $info = $this->shippingInfo->convert($result);

            // ...and placing to quote
            $this->extInfoHandler->setInfoToQuote($info, $quote);
        } catch (\Exception $e) {
            $this->generalHelper->addTolog('exception', $e->getMessage());
            throw new LocalizedException(__('Cannot process checkout data'));
        }
    }
}
