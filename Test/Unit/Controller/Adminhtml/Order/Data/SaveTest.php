<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Test\Unit\Controller\Adminhtml\Order\Data;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Messages;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Paazl\CheckoutWidget\Api\Data\Order\OrderReferenceInterface;
use Paazl\CheckoutWidget\Api\OrderReferenceRepositoryInterface;
use Paazl\CheckoutWidget\Controller\Adminhtml\Order\Data\Save;
use Paazl\CheckoutWidget\Model\Api\Builder\Order as OrderBuilder;
use Paazl\CheckoutWidget\Model\Api\Processor\SendToService;
use Paazl\CheckoutWidget\Model\Config;
use Paazl\CheckoutWidget\Model\ExtInfoHandler;
use Paazl\CheckoutWidget\Model\ShippingInfo;
use Paazl\CheckoutWidget\Model\ShippingInfoFactory;
use Paazl\CheckoutWidget\Test\Unit\UnitTestCase;

class SaveTest extends UnitTestCase
{
    /**
     * @var Save
     */
    private $entity;

    /**
     * @var RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $request;

    /**
     * @var ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $messageManager;

    /**
     * @var Json|\PHPUnit\Framework\MockObject\MockObject
     */
    private $jsonResult;

    /**
     * @var OrderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $order;

    /**
     * @var SendToService|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sendToService;

    /**
     * @var array
     */
    private $requestParams = [];

    protected function setUpWithoutVoid()
    {
        $this->requestParams = [
            'shipping_option' => 'shipping_option',
            'push_to_paazl' => true,
            'order_id' => 123,
            'isAjax' => true,
        ];

        $this->request = $this->createMock(RequestInterface::class);
        $this->request->method('getParam')->willReturnCallback(
            function ($key, $default = null) {
                return $this->requestParams[$key] ?? $default;
            }
        );

        $this->messageManager = $this->createMock(ManagerInterface::class);

        $this->jsonResult = $this->createMock(Json::class);

        $resultFactory = $this->createMock(ResultFactory::class);
        $resultFactory->method('create')->willReturn($this->jsonResult);

        $context = $this->createMock(Context::class);
        $context->method('getRequest')->willReturn($this->request);
        $context->method('getMessageManager')->willReturn($this->messageManager);
        $context->method('getResultFactory')->willReturn($resultFactory);

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->method('unserialize')->willReturn(
            ['shipping_option' => ['identifier' => 'id-1', 'name' => 'Home', 'rate' => 5.0]]
        );

        $this->order = $this->createMock(OrderInterface::class);
        $this->order->method('getEntityId')->willReturn(123);
        $this->order->method('setShippingDescription')->willReturnSelf();
        $this->order->method('setShippingAmount')->willReturnSelf();
        $this->order->method('setBaseShippingAmount')->willReturnSelf();

        $orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $orderRepository->method('get')->willReturn($this->order);

        $orderReference = $this->createMock(OrderReferenceInterface::class);

        $orderReferenceRepository = $this->createMock(OrderReferenceRepositoryInterface::class);
        $orderReferenceRepository->method('getByOrderId')->willReturn($orderReference);

        $shippingInfo = $this->createMock(ShippingInfo::class);
        $shippingInfo->method('toJson')->willReturn('{}');

        $extInfoHandler = $this->createMock(ExtInfoHandler::class);
        $extInfoHandler->method('getInfoFromOrderReference')->willReturn($shippingInfo);

        $scopeConfig = $this->createMock(Config::class);
        $scopeConfig->method('getCarrierTitle')->willReturn('Paazl');

        $this->sendToService = $this->createMock(SendToService::class);

        $messagesBlock = $this->createMock(Messages::class);
        $messagesBlock->method('getGroupedHtml')->willReturn('');

        $layout = $this->createMock(Layout::class);
        $layout->method('getMessagesBlock')->willReturn($messagesBlock);

        $layoutFactory = $this->createMock(LayoutFactory::class);
        $layoutFactory->method('create')->willReturn($layout);

        $this->entity = new Save(
            $context,
            $this->createMock(JsonFactory::class),
            $this->createMock(PageFactory::class),
            $orderRepository,
            $this->sendToService,
            $serializer,
            $layoutFactory,
            $this->createMock(Session::class),
            $orderReferenceRepository,
            $this->createMock(OrderBuilder::class),
            $extInfoHandler,
            $this->createMock(ShippingInfoFactory::class),
            $scopeConfig
        );
    }

    public function testSuccessfulSendReturnsSuccessResult()
    {
        $this->sendToService->expects($this->once())
            ->method('process')
            ->with($this->order, true);

        $this->messageManager->expects($this->never())->method('addErrorMessage');
        $this->messageManager->expects($this->exactly(2))->method('addSuccessMessage');

        $resultData = $this->executeAndCaptureResultData();

        $this->assertSame(false, $resultData['error']);
    }

    public function testInvalidArgumentExceptionReturnsErrorResult()
    {
        $this->sendToService->method('process')->willThrowException(new \InvalidArgumentException('Invalid argument'));

        $this->messageManager->expects($this->once())->method('addErrorMessage');

        $resultData = $this->executeAndCaptureResultData();

        $this->assertSame(true, $resultData['error']);
    }

    public function testGenericExceptionReturnsErrorResult()
    {
        $this->sendToService->method('process')->willThrowException(new \RuntimeException('Runtime error'));

        $this->messageManager->expects($this->once())->method('addErrorMessage');

        $resultData = $this->executeAndCaptureResultData();

        $this->assertSame(true, $resultData['error']);
    }

    /**
     * Runs the controller and returns the response data passed to the JSON result.
     *
     * @return array
     */
    private function executeAndCaptureResultData()
    {
        $resultData = null;
        $this->jsonResult->expects($this->once())
            ->method('setData')
            ->with(
                $this->callback(
                    function ($data) use (&$resultData) {
                        $resultData = $data;
                        return true;
                    }
                )
            )
            ->willReturnSelf();

        $this->entity->execute();

        return $resultData;
    }
}
