<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Controller\Adminhtml\Order\Data;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Backend\Model\Session;
use Paazl\CheckoutWidget\Controller\Adminhtml\Order;
use Paazl\CheckoutWidget\Model\Api\Processor\SendToService;

/**
 * Validate Data Controller
 */
class Validate extends Order
{
    const DEFAULT_MESSAGE_KEY = 'message';

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
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param PageFactory $resultPageFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param SendToService $sendToService
     * @param SerializerInterface $serializer
     * @param Session $session
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory,
        OrderRepositoryInterface $orderRepository,
        SendToService $sendToService,
        SerializerInterface $serializer,
        Session $session
    ) {
        parent::__construct(
            $context,
            $resultJsonFactory,
            $resultPageFactory,
            $orderRepository,
            $sendToService
        );
        $this->serializer = $serializer;
        $this->session = $session;
    }

    /**
     * Validate order data before send to Paazl
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $response = new DataObject();
        $response->setError(false);

        if (null === ($shippingOption = $this->getRequest()->getParam('shipping_option'))) {
            $message = __("The shipping option should be selected!!!");
            $this->setMessageToResponse($response, [$message]);
            $response->setError(true);

            return $this->resultJsonFactory->create()->setJsonData($response->toJson());
        }

        if (!($storedShippingOptions = $this->session->getPaazlShippingOptions())) {
            $message = __("Something went wrong during validation!!!");
            $this->setMessageToResponse($response, [$message]);
            $response->setError(true);

            return $this->resultJsonFactory->create()->setJsonData($response->toJson());
        }

        if (!isset($storedShippingOptions[$shippingOption])) {
            $message = __("Selected option wasn't store!!!");
            $this->setMessageToResponse($response, [$message]);
            $response->setError(true);

            return $this->resultJsonFactory->create()->setJsonData($response->toJson());
        }

        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
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
}
