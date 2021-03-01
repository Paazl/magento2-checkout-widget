<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Model\Quote;

use Exception;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Paazl\CheckoutWidget\Helper\General;
use Paazl\CheckoutWidget\Model\Api\Converter\ShippingOptions;
use Paazl\CheckoutWidget\Model\Api\Field\DeliveryType;
use Paazl\CheckoutWidget\Model\Api\PaazlApiFactory;
use Paazl\CheckoutWidget\Model\Carrier\Paazlshipping;
use Paazl\CheckoutWidget\Model\Checkout\WidgetConfigProvider;
use Paazl\CheckoutWidget\Model\ExtInfoHandler;

/**
 * Class CheckQuoteShippingOption
 *
 * @package Paazl\CheckoutWidget\Model\Quote
 */
class CheckQuoteShippingOption
{
    /**
     * @var ExtInfoHandler
     */
    private $extInfoHandler;

    /**
     * @var PaazlApiFactory
     */
    private $apiFactory;

    /**
     * @var WidgetConfigProvider
     */
    private $configProvider;

    /**
     * @var General
     */
    private $generalHelper;

    /**
     * @var ShippingOptions
     */
    private $shippingOptionsConverter;

    /**
     * CheckQuoteShippingOption constructor.
     *
     * @param ExtInfoHandler       $extInfoHandler
     * @param PaazlApiFactory      $apiFactory
     * @param WidgetConfigProvider $configProvider
     * @param General              $generalHelper
     * @param ShippingOptions      $shippingOptionsConverter
     */
    public function __construct(
        ExtInfoHandler $extInfoHandler,
        PaazlApiFactory $apiFactory,
        WidgetConfigProvider $configProvider,
        General $generalHelper,
        ShippingOptions $shippingOptionsConverter
    ) {
        $this->extInfoHandler = $extInfoHandler;
        $this->apiFactory = $apiFactory;
        $this->configProvider = $configProvider;
        $this->generalHelper = $generalHelper;
        $this->shippingOptionsConverter = $shippingOptionsConverter;
    }

    /**
     * @param Quote|CartInterface $quote
     * @return DataObject
     */
    public function validate(Quote $quote)
    {
        $result = new DataObject();
        if (!$quote->getShippingAddress()) {
            return $result;
        }

        $method = (string)$quote->getShippingAddress()->getShippingMethod();
        if (strpos($method, Paazlshipping::CODE) === false) {
            return $result;
        }

        $info = $this->extInfoHandler->getInfoFromQuote($quote);
        if (!$info) {
            return $result;
        }

        $optionIdentifier = $info->getIdenfifier();
        if (($info->getType() !== DeliveryType::DELIVERY) || empty($optionIdentifier)) {
            return $result;
        }

        // Re-retrieving shipping options from API
        $api = $this->apiFactory->create($quote->getStoreId());
        try {
            $config = $this->configProvider->setQuote($quote)->getConfig();

            $shippingOptions = $this->shippingOptionsConverter->convert(
                $api->getShippingOptions($config)
            );
        } catch (LocalizedException $e) {
            $this->generalHelper->addTolog('CheckQuoteShippingOption exception: ' . $e->getLogMessage(), [
                'quoteId' => $quote->getId()
            ]);
            return $result;
        } catch (Exception $e) {
            $this->generalHelper->addTolog('CheckQuoteShippingOption exception: ' . $e->getMessage(), [
                'quoteId' => $quote->getId()
            ]);
            return $result;
        }

        $options = $shippingOptions['shippingOptions'] ?? [];

        $optionExists = false;
        $optionExists = array_reduce($options, function ($carry, $item) use ($optionIdentifier) {
            return $carry || (!empty($item['identifier']) && ($item['identifier'] == $optionIdentifier));
        }, $optionExists);

        if (!$optionExists) {
            // Reset quote's shipping method to default one
            $result->setReloadWidget(true);
            return $result;
        }

        $newPrice = null;
        $newPrice = array_reduce($options, function ($carry, $item) use ($optionIdentifier) {
            return (!empty($item['identifier'])
                && ($item['identifier'] == $optionIdentifier)
                && array_key_exists('rate', $item))
                ? (float)$item['rate']
                : $carry;
        }, $newPrice);

        if ($newPrice !== null && $newPrice !== (float)$info->getPrice()) {
            // Price has changed - update info + recollect quote totals
            $info->setPrice($newPrice);
            $this->extInfoHandler->setInfoToQuote($info, $quote);
            $result->setRecollectTotals(true);
            return $result;
        }

        return $result;
    }
}
