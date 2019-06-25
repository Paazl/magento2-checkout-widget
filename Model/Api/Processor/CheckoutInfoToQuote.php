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
use Paazl\CheckoutWidget\Model\ExtInfoHandler;

/**
 * Class CheckoutInfoToQuote
 *
 * @package Paazl\CheckoutWidget\Model\Api\Processor
 */
class CheckoutInfoToQuote
{

    /**
     * @var PaazlApi
     */
    private $api;

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
     * @param PaazlApi       $api
     * @param Reference      $referenceBuilder
     * @param ToShippingInfo $shippingInfo
     * @param ExtInfoHandler $extInfoHandler
     * @param General        $generalHelper
     */
    public function __construct(
        PaazlApi $api,
        Reference $referenceBuilder,
        ToShippingInfo $shippingInfo,
        ExtInfoHandler $extInfoHandler,
        General $generalHelper
    ) {
        $this->api = $api;
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
            $result = $this->api->fetchCheckoutData($this->referenceBuilder->getQuoteReference($quote));

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
