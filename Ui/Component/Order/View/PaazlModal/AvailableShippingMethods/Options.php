<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Ui\Component\Order\View\PaazlModal\AvailableShippingMethods;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Backend\Model\Session;
use Paazl\CheckoutWidget\Model\Api\PaazlApiFactory;
use Paazl\CheckoutWidget\Model\Order\WidgetConfigProvider;
use Paazl\CheckoutWidget\Model\Api\ApiException;

/**
 * Paazl Shipping Options to Change Paazl Order Data
 */
class Options implements OptionSourceInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var array
     */
    protected $options = null;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var WidgetConfigProvider
     */
    protected $widgetConfigProvider;

    /**
     * @var PaazlApiFactory
     */
    protected $paazlApiFactory;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var Session
     */
    private $session;

    /**
     * Constructor
     *
     * @param RequestInterface         $request
     * @param OrderRepositoryInterface $orderRepository
     * @param WidgetConfigProvider     $widgetConfigProvider
     * @param PaazlApiFactory          $paazlApiFactory
     * @param SerializerInterface      $serializer
     * @param Session                  $session
     */
    public function __construct(
        RequestInterface $request,
        OrderRepositoryInterface $orderRepository,
        WidgetConfigProvider $widgetConfigProvider,
        PaazlApiFactory $paazlApiFactory,
        SerializerInterface $serializer,
        Session $session
    ) {
        $this->request = $request;
        $this->orderRepository = $orderRepository;
        $this->widgetConfigProvider = $widgetConfigProvider;
        $this->paazlApiFactory = $paazlApiFactory;
        $this->serializer = $serializer;
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getShippingOptions();
    }

    /**
     * Gets shipping options from Paazl and keeps it into session
     *
     * @return array|null
     * @throws ApiException
     */
    protected function getShippingOptions()
    {
        if ($this->options === null) {
            $order = $this->initOrder();
            if ($order) {
                $orderData = $this->widgetConfigProvider
                    ->setOrder($order)
                    ->getConfig();
                $shippingOptions = $this->serializer->unserialize(
                    $this->paazlApiFactory->create($order->getStoreId())
                        ->getShippingOptions($orderData)
                );
                $shippingOptions = $shippingOptions['shippingOptions'];
                $this->session->setPaazlShippingOptions($this->serializer->serialize($shippingOptions));
                $options = [];
                foreach ($shippingOptions as $id => $option) {
                    array_push($options, ['value' => $id, 'label' => $option['name']]);
                }

                $this->options = $options;
            }
        }

        return $this->options;
    }

    /**
     * Initialize order model instance
     *
     * @return bool|OrderInterface
     */
    protected function initOrder()
    {
        $orderId = $this->request->getParam('order_id');
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $e) {
            return false;
        } catch (InputException $e) {
            return false;
        }
        return $order;
    }
}
