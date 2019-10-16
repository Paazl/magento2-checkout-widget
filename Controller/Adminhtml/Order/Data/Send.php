<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Controller\Adminhtml\Order\Data;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Paazl\CheckoutWidget\Api\OrderReferenceRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Controller\ResultInterface;
use Paazl\CheckoutWidget\Model\Api\Processor\SendToService;
use Paazl\CheckoutWidget\Model\Api\ApiException;

/**
 * Controller Send
 */
class Send extends Action implements HttpPostActionInterface
{
    /** @var JsonFactory */
    protected $resultJsonFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /** @var SendToService */
    private $sendToService;

    /** @var OrderReferenceRepositoryInterface */
    private $orderReferenceRepository;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param PageFactory $resultPageFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param SendToService $sendToService
     * @param OrderReferenceRepositoryInterface $orderReferenceRepository
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory,
        OrderRepositoryInterface $orderRepository,
        SendToService $sendToService,
        OrderReferenceRepositoryInterface $orderReferenceRepository
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->orderRepository = $orderRepository;
        $this->sendToService = $sendToService;
        $this->orderReferenceRepository = $orderReferenceRepository;
    }

    /**
     * Send order data to Paazl
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $order = $this->_initOrder();

        if ($order) {
            try {
                $this->sendToService->process($order, true);
                return $this->resultPageFactory->create();
            } catch (LocalizedException $e) {
                $response = ['error' => true, 'message' => $e->getMessage()];
            } catch (ApiException $e) {
                $response = ['error' => true, 'message' => $e->getMessage()];
            }
            if (is_array($response)) {
                $resultJson = $this->resultJsonFactory->create();
                $resultJson->setData($response);
                return $resultJson;
            }
        }
        return $this->resultRedirectFactory->create()->setPath('sales/*/');
    }

    /**
     * Initialize order model instance
     *
     * @return bool|OrderInterface
     */
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        try {
            $order = $this->orderRepository->get($id);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            return false;
        } catch (InputException $e) {
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            return false;
        }
        return $order;
    }
}
