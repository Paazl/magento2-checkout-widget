<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order;
use Magento\Framework\Registry;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Paazl\CheckoutWidget\Model\Api\ApiException;
use Paazl\CheckoutWidget\Model\Api\Processor\SendToService;
use Psr\Log\LoggerInterface;

/**
 * Resend Controller
 */
class Retry extends Order
{
    /** @var SendToService */
    private $sendToService;

    /**
     * Constructor
     *
     * @param SendToService $sendToService
     * @param Action\Context $context
     * @param Registry $coreRegistry
     * @param FileFactory $fileFactory
     * @param InlineInterface $translateInline
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param RawFactory $resultRawFactory
     * @param OrderManagementInterface $orderManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        SendToService $sendToService,
        Action\Context $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        InlineInterface $translateInline,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        LayoutFactory $resultLayoutFactory,
        RawFactory $resultRawFactory,
        OrderManagementInterface $orderManagement,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger
    ) {
        $this->sendToService = $sendToService;
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $translateInline,
            $resultPageFactory,
            $resultJsonFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $orderManagement,
            $orderRepository,
            $logger
        );
    }

    /**
     * Sends order data to Paazl
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $order = $this->_initOrder();

        if ($order) {
            try {
                $this->sendToService->process($order);

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
}
