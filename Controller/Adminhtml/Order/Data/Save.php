<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Controller\Adminhtml\Order\Data;

use Magento\Backend\App\Action;
use Magento\Backend\Model\Session\Proxy as Session;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Paazl\CheckoutWidget\Api\OrderReferenceRepositoryInterface;
use Paazl\CheckoutWidget\Controller\Adminhtml\Order;
use Paazl\CheckoutWidget\Model\Api\Field\DeliveryType;
use Paazl\CheckoutWidget\Model\Api\Processor\SendToService;
use Paazl\CheckoutWidget\Model\Api\PaazlApi;
use Paazl\CheckoutWidget\Model\Api\Builder\Order as OrderBuilder;
use Paazl\CheckoutWidget\Model\ExtInfoHandler;
use Paazl\CheckoutWidget\Model\Config;

/**
 * Edit Data Controller
 */
class Save extends Order
{
    const DEFAULT_MESSAGE_KEY = 'message';

    /** @var PaazlApi */
    protected $paazlApi;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var LayoutFactory */
    private $layoutFactory;

    /** @var Session */
    private $session;

    /** @var OrderReferenceRepositoryInterface */
    private $orderReferenceRepository;

    /** @var OrderBuilder */
    private $orderBuilder;

    /** @var ExtInfoHandler */
    private $extInfoHandler;

    private $scopeConfig;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param PageFactory $resultPageFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param SendToService $sendToService
     * @param PaazlApi $paazlApi
     * @param SerializerInterface $serializer
     * @param LayoutFactory $layoutFactory
     * @param Session $session
     * @param OrderReferenceRepositoryInterface $orderReferenceRepository
     * @param OrderBuilder $orderBuilder
     * @param ExtInfoHandler $extInfoHandler
     * @param Config $scopeConfig
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory,
        OrderRepositoryInterface $orderRepository,
        SendToService $sendToService,
        PaazlApi $paazlApi,
        SerializerInterface $serializer,
        LayoutFactory $layoutFactory,
        Session $session,
        OrderReferenceRepositoryInterface $orderReferenceRepository,
        OrderBuilder $orderBuilder,
        ExtInfoHandler $extInfoHandler,
        Config $scopeConfig
    ) {
        parent::__construct(
            $context,
            $resultJsonFactory,
            $resultPageFactory,
            $orderRepository,
            $sendToService
        );
        $this->paazlApi = $paazlApi;
        $this->serializer = $serializer;
        $this->layoutFactory = $layoutFactory;
        $this->session = $session;
        $this->orderReferenceRepository = $orderReferenceRepository;
        $this->orderBuilder = $orderBuilder;
        $this->extInfoHandler = $extInfoHandler;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Saves selected shipping option to order
     *
     * @return Redirect|ResponseInterface|Json|ResultInterface
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function execute()
    {
        try {
            $storedShippingOptions = $this->serializer->unserialize($this->session->getPaazlShippingOptions());
            $shippingOption = $storedShippingOptions[$this->getRequest()->getParam('shipping_option')];
            $order = $this->_initOrder();
            $orderReference = $this->orderReferenceRepository->getByOrderId($order->getEntityId());
            $shippingInfo = $this->extInfoHandler->getInfoFromOrderReference($orderReference);
            $shippingInfo->setType(DeliveryType::DELIVERY);
            $shippingInfo->setIdenfifier($shippingOption['identifier']);
            $shippingInfo->setOptionTitle($shippingOption['name']);
            $shippingDescription = $this->scopeConfig->getCarrierTitle() . ' - ' . $shippingOption['name'];
            $shippingRate = $shippingOption['rate'];
            $order->setShippingDescription($shippingDescription)
                ->setShippingAmount($shippingRate)
                ->setBaseShippingAmount($shippingRate);
            $orderReference->setExtShippingInfo($shippingInfo->toJson());
            $this->orderReferenceRepository->save($orderReference);
            $this->orderRepository->save($order);
            $this->messageManager->addSuccessMessage(__(
                "Paazl order data have saved successfully."
            ));
        } catch (\InvalidArgumentException $e) {
            $message = __("Paazl order data couldn't be saved due to an error. Verify your information and try again. "
                . "If the error persists, please try again later.");
            $this->messageManager->addErrorMessage($message);

            return $this->returnResult('sales/order/view', ['_current' => true], ['error' => true]);
        }

        if (!$this->getRequest()->getParam('push_to_paazl')) {
            return $this->returnResult('sales/order/view', [], ['error' => false]);
        }

        try {
            $this->sendToService->process($order, true);
            $this->messageManager->addSuccessMessage(__(
                "Order data have sent to Paazl successfully."
            ));
            return $this->returnResult('sales/order/view', [], ['error' => false]);
        } catch (\InvalidArgumentException $e) {
            $message = __("Order data couldn't be sent to Paazl due to an error. Please try again later. "
                . "If the error persists, please try again later.");
            $this->messageManager->addErrorMessage($message);

            return $this->returnResult('sales/order/view', ['_current' => true], ['error' => true]);
        } finally {
            $message = __("Order data couldn't be sent to Paazl due to an error. Please try again later. "
                . "If the error persists, please try again later.");
            $this->messageManager->addErrorMessage($message);

            return $this->returnResult('sales/order/view', ['_current' => true], ['error' => true]);
        }
    }

    /**
     * Set message to response object
     *
     * @param DataObject $response
     * @param string[] $messages
     * @return DataObject
     */
    private function setMessageToResponse($response, $messages)
    {
        $messageKey = $this->getRequest()->getParam('message_key', static::DEFAULT_MESSAGE_KEY);
        if ($messageKey === static::DEFAULT_MESSAGE_KEY) {
            $messages = reset($messages);
        }
        return $response->setData($messageKey, $messages);
    }

    /**
     * Provides an initialized Result object.
     *
     * @param string $path
     * @param array $params
     * @param array $response
     * @return Json|Redirect
     */
    private function returnResult($path = '', array $params = [], array $response = [])
    {
        if ($this->isAjax()) {
            $layout = $this->layoutFactory->create();
            $layout->initMessages();

            $response['messages'] = [$layout->getMessagesBlock()->getGroupedHtml()];
            $response['params'] = $params;
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($response);
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath($path, $params);
    }

    /**
     * Define whether request is Ajax
     *
     * @return boolean
     */
    private function isAjax()
    {
        return $this->getRequest()->getParam('isAjax');
    }
}
