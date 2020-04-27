<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Api\Builder;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Item;
use Paazl\CheckoutWidget\Api\OrderReferenceRepositoryInterface;
use Paazl\CheckoutWidget\Model\Api\Builder\Reference as Reference;
use Paazl\CheckoutWidget\Model\Api\Field\DeliveryType;
use Paazl\CheckoutWidget\Model\Config;
use Paazl\CheckoutWidget\Model\ExtInfoHandler;
use Paazl\CheckoutWidget\Model\Handler\Item as ItemHandler;

/**
 * Class Order
 *
 * @package Paazl\CheckoutWidget\Model\Api\Builder
 */
class Order
{
    /**
     * @var ExtInfoHandler
     */
    private $extInfoHandler;

    /**
     * @var Reference
     */
    private $referenceBuilder;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var OrderReferenceRepositoryInterface
     */
    private $orderReferenceRepository;

    /**
     * @var ItemHandler
     */
    private $itemHandler;

    /**
     * Order constructor.
     *
     * @param ExtInfoHandler                    $extInfoHandler
     * @param Reference                         $referenceBuilder
     * @param Config                            $config
     * @param OrderReferenceRepositoryInterface $orderReferenceRepository
     * @param ItemHandler                       $itemHandler
     */
    public function __construct(
        ExtInfoHandler $extInfoHandler,
        Reference $referenceBuilder,
        Config $config,
        OrderReferenceRepositoryInterface $orderReferenceRepository,
        ItemHandler $itemHandler
    ) {
        $this->extInfoHandler = $extInfoHandler;
        $this->referenceBuilder = $referenceBuilder;
        $this->config = $config;
        $this->orderReferenceRepository = $orderReferenceRepository;
        $this->itemHandler = $itemHandler;
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

        $result = [
            'additionalInstruction' => $order->getCustomerNote(),
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
                    'street'               => $address['street'],
                    'houseNumber'          => $address['houseNumber'],
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
            'insuredValue'          => [
                'currency' => $order->getOrderCurrencyCode(),
                'value'    => $order->getGrandTotal(),
            ],
            'requestedDeliveryDate' => $extInformation->getPreferredDeliveryDate(),
            'products'              => [],
            'reference'             => $this->referenceBuilder->getOrderReference($order),
            'invoiceNumber'         => $this->referenceBuilder->getOrderReference($order),
            'shipping'              => [
                'option' => $extInformation->getIdenfifier()
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
        $street = implode(' ', $shippingAddress->getStreet());
        $street = trim(str_replace("\t", ' ', $street));
        $houseExtensionPattern = '(?<houseNumber>\d{1,5})[[:punct:]\-\/\s]*(?<houseNumberExtension>[^[:space:]]{1,2})?';
        $streetPattern = '(?<street>.+)';

        $patterns = [
            "/^{$streetPattern}[\s\R[:space:]]+{$houseExtensionPattern}$/",
            "/^{$houseExtensionPattern}[\s\R[:space:]]+{$streetPattern}$/",
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

        throw new LocalizedException(
            __('This order cannot be committed to Paazl, please make sure the address has a valid housenumber.')
        );
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
            if ($item->getProductType() !== Type::TYPE_SIMPLE && $item->getProductType() !== Grouped::TYPE_CODE) {
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

        if ($widthAttribute = $this->config->getProductAttributeWidth()) {
            if ($width = $product->getData($widthAttribute)) {
                $dimensionArray['width'] = (int)$width;
            }
        }

        if ($heightAttribute = $this->config->getProductAttributeHeight()) {
            if ($height = $product->getData($heightAttribute)) {
                $dimensionArray['height'] = (int)$height;
            }
        }

        if ($lengthAttribute = $this->config->getProductAttributeLength()) {
            if ($length = $product->getData($lengthAttribute)) {
                $dimensionArray['length'] = (int)$length;
            }
        }

        return $dimensionArray;
    }
}
