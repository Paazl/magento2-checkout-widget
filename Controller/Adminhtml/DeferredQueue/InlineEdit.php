<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Controller\Adminhtml\DeferredQueue;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Paazl\CheckoutWidget\Model\DeferredQueue\DeferredQueue;
use Paazl\CheckoutWidget\Model\DeferredQueue\DeferredQueueFactory;
use Paazl\CheckoutWidget\Model\ResourceModel\DeferredQueue\DeferredQueue as DeferredQueueResource;

/**
 * Class InlineEdit
 *
 * @package Paazl\CheckoutWidget\Controller\Adminhtml\DeferredQueue
 */
class InlineEdit extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level
     */
    public const ADMIN_RESOURCE = 'Paazl_CheckoutWidget::deferred_queue';

    private JsonFactory $jsonFactory;
    private DeferredQueueResource $deferredQueueResource;
    private DeferredQueueFactory $deferredQueueFactory;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        DeferredQueueResource $deferredQueueResource,
        DeferredQueueFactory $deferredQueueFactory
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->deferredQueueResource = $deferredQueueResource;
        $this->deferredQueueFactory = $deferredQueueFactory;
    }

    /**
     * Inline edit action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $entityId) {
            try {
                $deferredQueue = $this->deferredQueueFactory->create();
                $this->deferredQueueResource->load($deferredQueue, $entityId);

                if (isset($postItems[$entityId]['deferred_status'])) {
                    $deferredQueue->setDeferredStatus($postItems[$entityId]['deferred_status']);
                    $this->deferredQueueResource->save($deferredQueue);
                }
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithEntityId(
                    $deferredQueue,
                    __($e->getMessage())
                );
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add entity id to error message
     *
     * @param DeferredQueue $deferredQueue
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithEntityId(DeferredQueue $deferredQueue, $errorText)
    {
        return '[ID: ' . $deferredQueue->getEntityId() . '] ' . $errorText;
    }
}
