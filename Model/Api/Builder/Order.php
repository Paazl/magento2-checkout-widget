<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Api\Builder;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Item;
use Paazl\CheckoutWidget\Api\OrderReferenceRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Paazl\CheckoutWidget\Model\Api\ApiException;
use Paazl\CheckoutWidget\Model\Api\Builder\Reference as Reference;
use Paazl\CheckoutWidget\Model\Api\Field\DeliveryType;
use Paazl\CheckoutWidget\Model\Api\PaazlApiFactory;
use Paazl\CheckoutWidget\Model\Config;
use Paazl\CheckoutWidget\Model\ExtInfoHandler;
use Paazl\CheckoutWidget\Model\Handler\Item as ItemHandler;
use Paazl\CheckoutWidget\Model\Order\WidgetConfigProvider;
use Paazl\CheckoutWidget\Model\System\Config\Source\DimensionsMetric;
use Paazl\CheckoutWidget\Helper\General;

/**
 * Class Order
 */
class Order
{

    private ExtInfoHandler $extInfoHandler;
    private Reference $referenceBuilder;
    private Config $config;
    private OrderReferenceRepositoryInterface $orderReferenceRepository;
    private CartRepositoryInterface $quoteRepository;
    private ItemHandler $itemHandler;
    private WidgetConfigProvider $widgetConfigProvider;
    private PaazlApiFactory $paazlApiFactory;
    private SerializerInterface $serializer;
    private General $generalHelper;

    public function __construct(
        ExtInfoHandler $extInfoHandler,
        Reference $referenceBuilder,
        Config $config,
        OrderReferenceRepositoryInterface $orderReferenceRepository,
        CartRepositoryInterface $quoteRepository,
        ItemHandler $itemHandler,
        WidgetConfigProvider $widgetConfigProvider,
        PaazlApiFactory $paazlApiFactory,
        SerializerInterface $serializer,
        General $generalHelper
    ) {
        $this->extInfoHandler = $extInfoHandler;
        $this->referenceBuilder = $referenceBuilder;
        $this->config = $config;
        $this->orderReferenceRepository = $orderReferenceRepository;
        $this->quoteRepository = $quoteRepository;
        $this->itemHandler = $itemHandler;
        $this->widgetConfigProvider = $widgetConfigProvider;
        $this->paazlApiFactory = $paazlApiFactory;
        $this->serializer = $serializer;
        $this->generalHelper = $generalHelper;
    }

    /**
     * Builds order in format supported by Paazl
     *
     * @param OrderInterface $order
     *
     * @return array
     * @throws LocalizedException
     * @throws NotFoundException
     */
    public function getCreateOrderData(OrderInterface $order)
    {
        try {
            $reference = $this->orderReferenceRepository->getByOrderId($order->getEntityId());
        } catch (NoSuchEntityException $e) {
            throw new NotFoundException(__('Reference information not found'));
        }

        $extInformation = $this->extInfoHandler->getInfoFromOrderReference($reference);
        if ($extInformation === null) {
            throw new NotFoundException(__('Reference information not found'));
        }

        /** @var Address $shippingAddress */
        $shippingAddress = $order->getShippingAddress();
        if ($extInformation->getType() === DeliveryType::PICKUP) {
            $shippingAddress = $order->getBillingAddress();
        }
        $address = $this->parseAddress($shippingAddress);
        $defaultHouseNumber = $this->config->getHouseNumberDefaultOption() ? 0 : '';

        $result = [
            'additionalInstruction' => '',
            'consignee'             => [
                'email'       => $order->getCustomerEmail(),
                'name'        => $shippingAddress->getName(),
                'companyName' => $shippingAddress->getCompany(),
                'phone'       => $shippingAddress->getTelephone(),
                'address'     => [
                    'city'                 => $shippingAddress->getCity(),
                    'country'              => $shippingAddress->getCountryId(),
                    'postalCode'           => $shippingAddress->getPostcode(),
                    'province'             => $shippingAddress->getRegionCode(),
                    'street'               => $address['street'] ?? '',
                    'houseNumber'          => $address['houseNumber'] ?? $defaultHouseNumber,
                    'houseNumberExtension' => $address['houseNumberExtension'] ?? '',
                ]
            ],
            'customsValue'          => [
                'currency' => $order->getOrderCurrencyCode(),
                'value'    => $order->getGrandTotal(),
            ],
            'codValue'              => [
                'currency' => $order->getOrderCurrencyCode(),
                'value'    => $order->getGrandTotal(),
            ],
            'insuredValue'        => [
                'currency' => $order->getOrderCurrencyCode(),
                'value'    => $this->config->getInsuranceValue($order->getStoreId()),
            ],
            'requestedDeliveryDate' => $extInformation->getPreferredDeliveryDate(),
            'products'              => [],
            'reference'             => $this->referenceBuilder->getOrderReference($order),
            'invoiceNumber'         => $this->referenceBuilder->getOrderReference($order),
            'shipping'              => [
                'option' => $this->getShippingOption($extInformation->getIdenfifier(), $order)
            ],
        ];

        /**
         * Added fallback to streetLines if no address could be parsed.
         */
        if (empty($result['consignee']['address']['street'])) {
            $result['consignee']['address']['streetLines'] = $shippingAddress->getStreet();
            unset($result['consignee']['address']['street']);
            unset($result['consignee']['address']['houseNumber']);
            unset($result['consignee']['address']['houseNumberExtension']);
        }

        if ($extInformation->getType() === DeliveryType::PICKUP) {
            $result['shipping']['pickupLocation'] = [
                'code' => $extInformation->getLocationCode()
            ];

            if ($extInformation->getLocationAccountNumber()) {
                $result['shipping']['pickupLocation']['accountNumber'] = $extInformation->getLocationAccountNumber();
            }
        }

        if ($order->getWeight()) {
            $result['weight'] = $order->getWeight();
        }

        $result['products'] = $this->getProducts($order);

        return $result;
    }

    /**
     * @param Address $shippingAddress
     *
     * @return array
     * @throws LocalizedException
     */
    public function parseAddress(Address $shippingAddress)
    {
        $street = $shippingAddress->getStreet();
        if ($this->config->housenumberExtensionOnThirdStreet()) {
            return [
                'street'               => trim($street[0] ?? ''),
                'houseNumber'          => trim($street[1] ?? ''),
                'houseNumberExtension' => trim($street[2] ?? ''),
            ];
        }
        $street = implode(' ', $street);
        $street = trim(str_replace("\t", ' ', $street));
        $houseExtensionPattern = '(?<houseNumber>\d{1,5})[[:punct:]\-\/\s]*(?<houseNumberExtension>[^[:space:]]{1,2})?';
        $streetPattern = '(?<street>.+)';

        $patterns = [
            "/^{$streetPattern}[\s[:space:]]+{$houseExtensionPattern}$/",
            "/^{$houseExtensionPattern}[\s[:space:]]+{$streetPattern}$/",
        ];

        $result = null;

        foreach ($patterns as $pattern) {
            if (!preg_match($pattern, $street, $matches)) {
                continue;
            }

            return [
                'street'               => trim($matches['street'] ?? ''),
                'houseNumber'          => trim($matches['houseNumber'] ?? ''),
                'houseNumberExtension' => trim($matches['houseNumberExtension'] ?? ''),
            ];
        }

        $streetToParse = explode(' ', $street);
        $probably = [
            'street' => [],
            'houseNumber' => [],
            'houseNumberExtension' => []
        ];

        if (ctype_digit(end($streetToParse))) {
            $probably = [
                'houseNumber' => array_pop($streetToParse),
                'street' => implode(' ', $streetToParse),
                'houseNumberExtension' => ''
            ];
            if ($probably['street'] && $probably['houseNumber']) {
                return $probably;
            }
        }
        foreach ($streetToParse as $parser) {
            if (!trim($parser)) {
                continue;
            }
            if (!preg_match('~[0-9]+~', $parser)
                && strlen($parser) > 2) {
                $probably['street'][] = $parser;
            } elseif (ctype_digit($parser)) {
                $probably['houseNumber'][] = $parser;
            } else {
                $num = '';
                foreach (str_split($parser) as $index => $char) {
                    if (is_numeric($char)) {
                        $num .= $char;
                    } else {
                        $probably['houseNumber'][] = $num;
                        $probably['houseNumberExtension'][] = substr($parser, $index, strlen($parser) - $index);
                        break;
                    }
                }
            }
        }

        $probably = [
            'street' => is_array($probably['street'])
                ? implode(' ', $probably['street'])
                : $probably['street'],
            'houseNumber' => is_array($probably['houseNumber'])
                ? implode('', $probably['houseNumber'])
                : $probably['houseNumber'],
            'houseNumberExtension' => is_array($probably['houseNumberExtension'])
                ? implode('', $probably['houseNumberExtension'])
                : $probably['houseNumberExtension']
        ];
        if ($probably['street'] && $probably['houseNumber']) {
            return $probably;
        }

        if ($this->config->getHouseNumberDefaultOption()) {
            return [
                'street' => $street,
                'houseNumber' => null,
                'houseNumberExtension' => null,
            ];
        } else {
            throw new LocalizedException(
                __('This order cannot be committed to Paazl, please make sure the address has a valid housenumber.')
            );
        }
    }

    /**
     * @param OrderInterface $order
     *
     * @return array
     * @throws LocalizedException
     */
    private function getProducts(OrderInterface $order)
    {
        $products = [];

        /** @var Item $item */
        foreach ($order->getItems() as $item) {
            if ($item->getProductType() !== Type::TYPE_SIMPLE
                && $item->getProductType() !== Type::TYPE_BUNDLE
                && $item->getProductType() !== Grouped::TYPE_CODE
            ) {
                continue;
            }

            // Skip a simple product if the parent is a bundle
            if ($this->hasBundleParent($item)) {
                continue;
            }

            $itemData = [
                'quantity'             => (int)$item->getQtyOrdered(),
                'unitPrice'            => [
                    'value'    => $this->itemHandler->getPriceValue($item),
                    'currency' => $order->getOrderCurrencyCode()
                ],
                'description'          => $this->getItemDescription($item),
                'countryOfManufacture' => $this->getCountryOfManufacture($item),
                'hsTariffCode'         => $this->getHsTariffCode($item),
            ];

            if ($item->getWeight() > 0) {
                $itemData['weight'] = $item->getWeight();
            }

            if ($dimensions = $this->getProductDimemension($item)) {
                // phpcs:ignore Magento2.Performance
                $itemData = array_merge($itemData, $dimensions);
            }

            $products[] = $itemData;
        }

        return $products;
    }

    /**
     * @param Item $item
     *
     * @return mixed|null|string
     */
    private function getItemDescription(Item $item)
    {
        if ($customDescription = $this->config->getCustomDescription()) {
            return $customDescription;
        }

        return $item->getName();
    }

    /**
     * @param Item $item
     *
     * @return mixed
     */
    private function getCountryOfManufacture(Item $item)
    {
        $product = $item->getProduct();
        if ($countryOfManufacture = $this->config->getProductAttributeCountyOfManufacture()) {
            if ($countryOfManufacture == 'paazl-static-option') {
                return $this->config->getProductStaticCountyOfManufacture();
            }
            return $product->getData($countryOfManufacture);
        }
    }

    /**
     * @param Item $item
     *
     * @return mixed
     */
    private function getHsTariffCode(Item $item)
    {
        $product = $item->getProduct();
        if ($hsTarrifCode = $this->config->getProductAttributeHsTariffCode()) {
            if ($hsTarrifCode == 'paazl-static-option') {
                return $this->config->getProductStaticHsTariffCode();
            }
            return $product->getData($hsTarrifCode);
        }
    }

    /**
     * @param Item $item
     *
     * @return array
     */
    private function getProductDimemension(Item $item)
    {
        $dimensionArray = [];
        $product = $item->getProduct();

        switch ($this->config->getDimensionsMetric()) {
            case DimensionsMetric::METRIC_MM:
                $k = 0.1;
                break;
            case DimensionsMetric::METRIC_CM:
                $k = 1;
                break;
            case DimensionsMetric::METRIC_M:
                $k = 100;
                break;
            default:
                $k = 1;
        }

        if ($widthAttribute = $this->config->getProductAttributeWidth()) {
            if ($width = $this->reformatVolumeData($product->getData($widthAttribute)) * $k) {
                $dimensionArray['width'] = (int)$width;
            }
        }

        if ($heightAttribute = $this->config->getProductAttributeHeight()) {
            if ($height = $this->reformatVolumeData($product->getData($heightAttribute)) * $k) {
                $dimensionArray['height'] = (int)$height;
            }
        }

        if ($lengthAttribute = $this->config->getProductAttributeLength()) {
            if ($length = $this->reformatVolumeData($product->getData($lengthAttribute)) * $k) {
                $dimensionArray['length'] = (int)$length;
            }
        }

        return $dimensionArray;
    }

    /**
     * @param $value
     * @return float
     */
    private function reformatVolumeData($value): float
    {
        if ($value == null) {
            return 0.00;
        }

        return (float)str_replace(',', '.', $value);
    }

    /**
     * Check if parent item is bundle
     *
     * @param $item
     * @return bool
     */
    private function hasBundleParent($item): bool
    {
        if ($item->getParentItemId() > 0) {
            return $item->getParentItem()->getProductType() === Type::TYPE_BUNDLE;
        }
        return false;
    }

    /**
     * @param $identifier
     * @param OrderInterface $order
     * @return mixed|string
     * @throws LocalizedException
     * @throws ApiException
    */
    private function getShippingOption($identifier, OrderInterface $order)
    {
        $this->generalHelper->addTolog('SHIPPING OPTION $identifier', $identifier);
        if ($identifier !== null) {
            return $identifier;
        }

        try {
            $quote = $this->quoteRepository->get((int)$order->getQuoteId());
            $this->generalHelper->addTolog('SHIPPING OPTION $quoteId', $quote->getId());
        } catch (NoSuchEntityException $e) {
            return '';
        }

        if ($quote == null) {
            return '';
        }

        $extInformation = $this->extInfoHandler->getInfoFromQuote($quote);
        $this->generalHelper->addTolog('SHIPPING OPTION $extInformation', $extInformation);
        if (($extInformation !== null) && ($identifier = $extInformation->getIdenfifier())) {
            return $identifier;
        }

        //get identifier from api using shipping description
        $shippingDescription = $order->getShippingDescription();
        $this->generalHelper->addTolog('SHIPPING OPTION $shippingDescription', $shippingDescription);
        $carrierTitle = $this->config->getCarrierTitle() . ' - ';
        $this->generalHelper->addTolog('SHIPPING OPTION $carrierTitle', $carrierTitle);
        $shippingDescription = str_replace($carrierTitle, '', $shippingDescription);
        $this->generalHelper->addTolog('SHIPPING OPTION $shippingDescription', $shippingDescription);

        $orderData = $this->widgetConfigProvider
            ->setOrder($order)
            ->getConfig();
        $this->generalHelper->addTolog('SHIPPING OPTION $orderData', $orderData);
        $shippingOptions = $this->serializer->unserialize(
            $this->paazlApiFactory->create($order->getStoreId())
                ->getShippingOptions($orderData)
        );
        $this->generalHelper->addTolog('SHIPPING OPTION $shippingOptions', $shippingOptions);
        $shippingOptions = $shippingOptions['shippingOptions'] ?? [];
        foreach ($shippingOptions as $shippingOption) {
            if (isset($shippingOption['name'], $shippingOption['identifier'])) {
                if ($shippingOption['name'] == $shippingDescription) {
                    return $shippingOption['identifier'];
                }
            }
        }
        return '';
    }
}
