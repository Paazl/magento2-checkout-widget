<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\ViewModel\Pickup;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use Magento\Framework\Phrase;
use Paazl\CheckoutWidget\Api\OrderReferenceRepositoryInterface;
use Paazl\CheckoutWidget\Api\Data\Order\OrderReferenceInterface;
use Paazl\CheckoutWidget\Helper\Order as OrderHelper;
use Paazl\CheckoutWidget\Model\Api\Field\DeliveryType;
use Paazl\CheckoutWidget\Model\ExtInfoHandler;
use Paazl\CheckoutWidget\Model\Delivery\OrderPickupLocationFactory;
use Paazl\CheckoutWidget\Model\ShippingInfo;
use Paazl\CheckoutWidget\Model\Location\OrderAddressFactory;

/**
 * View model for Pickup related blocks.
 */
class Info implements ArgumentInterface
{
    /**
     * @var OrderInterface
     */
    private $order = null;

    /**
     * @var ShippingInfo
     */
    protected $orderInfo = null;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var OrderHelper
     */
    protected $orderHelper;

    /**
     * @var OrderReferenceRepositoryInterface
     */
    private $orderReferenceRepository;

    /**
     * @var ExtInfoHandler
     */
    protected $extInfoHandler;

    /**
     * @var OrderPickupLocationFactory $orderPickupLocationFactory
     */
    protected $orderPickupLocationFactory;

    /**
     * @var OrderAddressFactory
     */
    private $orderAddressFactory;

    /**
     * @var AddressRenderer
     */
    private $addressRenderer;

    /**
     * ViewModel Constructor.
     *
     * @param RequestInterface $request
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderHelper $orderHelper
     * @param OrderReferenceRepositoryInterface $orderReferenceRepository
     * @param ExtInfoHandler $extInfoHandler
     * @param OrderPickupLocationFactory $orderPickupLocationFactory
     * @param OrderAddressFactory $orderAddressFactory
     * @param AddressRenderer $addressRenderer
     */
    public function __construct(
        RequestInterface $request,
        OrderRepositoryInterface $orderRepository,
        OrderHelper $orderHelper,
        OrderReferenceRepositoryInterface $orderReferenceRepository,
        ExtInfoHandler $extInfoHandler,
        OrderPickupLocationFactory $orderPickupLocationFactory,
        OrderAddressFactory $orderAddressFactory,
        AddressRenderer $addressRenderer
    ) {
        $this->request = $request;
        $this->orderRepository = $orderRepository;
        $this->orderHelper = $orderHelper;
        $this->orderReferenceRepository = $orderReferenceRepository;
        $this->extInfoHandler = $extInfoHandler;
        $this->orderPickupLocationFactory = $orderPickupLocationFactory;
        $this->orderAddressFactory = $orderAddressFactory;
        $this->addressRenderer = $addressRenderer;
    }

    /**
     * Gets order by Order Id request param
     *
     * @return OrderInterface|Order
     */
    public function getOrder()
    {
        if (null === $this->order) {
            if ($orderId = $this->request->getParam('order_id')) {
                $this->order = $this->orderRepository->get($orderId);
            }
        }

        return $this->order;
    }

    /**
     * Gets Order reference
     *
     * @return OrderReferenceInterface
     * @throws NoSuchEntityException
     */
    public function getOrderReference()
    {
        return $this->orderReferenceRepository->getByOrderId($this->getOrder()->getEntityId());
    }

    /**
     * Gets Pickup Location Address HTML
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPickupLocationAddressHtml()
    {
        $order = $this->getOrder();

        if (!$order->getId() || $order->getIsVirtual() || !$this->orderHelper->isPaazlOrder($order)) {
            return '';
        }

        /** @var ShippingInfo|null $orderInfo */
        $orderInfo = $this->getOrderInfo();

        if (!$orderInfo || ($orderInfo->getType() !== DeliveryType::PICKUP)) {
            return '';
        }

        $pickupAddress = $orderInfo->getPickupAddress();

        $street = [];
        $street[] = $pickupAddress['street'];
        $street[] = $pickupAddress['streetNumber'] ?? '';
        $street = trim(implode(' ', $street));

        $addressData = [
            'postcode'          => $pickupAddress['postalCode'],
            'country'           => $pickupAddress['country'],
            'city'              => $pickupAddress['city'],
            'street'            => $street
        ];

        $pickupLocation = $this->orderPickupLocationFactory->create(['data' => $addressData]);

        /** @var Address $address */
        $address = $this->orderAddressFactory->createFromPickupLocation($pickupLocation);
        $formattedAddress = $this->addressRenderer->format($address, 'html');
        return (string)$formattedAddress;
    }

    /**
     * Gets Shipping Information
     *
     * @return ShippingInfo|null
     * @throws NoSuchEntityException
     */
    protected function getOrderInfo()
    {
        if (null === $this->orderInfo) {
            $this->orderInfo = $this->extInfoHandler->getInfoFromOrderReference($this->getOrderReference());
        }

        return $this->orderInfo;
    }

    /**
     * @return Phrase
     */
    public function getPickupTitle()
    {
        return __('Ship To');
    }

    /**
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getLocationName()
    {
        return $this->getOrderInfo()->getLocationName();
    }

    /**
     * @return string
     */
    public function getLocationCode()
    {
        try {
            return (string)$this->getOrderInfo()->getLocationCode();
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getCarrierDescription()
    {
        try {
            return (string)$this->getOrderInfo()->getCarrierDescription();
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }
}
