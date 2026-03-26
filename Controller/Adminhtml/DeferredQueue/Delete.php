<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Controller\Adminhtml\DeferredQueue;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Paazl\CheckoutWidget\Model\DeferredQueue\DeferredQueue;
use Paazl\CheckoutWidget\Model\DeferredQueue\DeferredQueueFactory;
use Paazl\CheckoutWidget\Model\ResourceModel\DeferredQueue\DeferredQueue as DeferredQueueResource;

/**
 * Class Delete
 *
 * @package Paazl\CheckoutWidget\Controller\Adminhtml\DeferredQueue
 */
class Delete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level
     */
    public const ADMIN_RESOURCE = 'Paazl_CheckoutWidget::deferred_queue';

    private DeferredQueueResource $deferredQueueResource;
    private DeferredQueueFactory $deferredQueueFactory;

    public function __construct(
        Context $context,
        DeferredQueueResource $deferredQueueResource,
        DeferredQueueFactory $deferredQueueFactory
    ) {
        parent::__construct($context);
        $this->deferredQueueResource = $deferredQueueResource;
        $this->deferredQueueFactory = $deferredQueueFactory;
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');

        if ($id) {
            try {
                $deferredQueue = $this->deferredQueueFactory->create();
                $this->deferredQueueResource->load($deferredQueue, $id);
                $this->deferredQueueResource->delete($deferredQueue);
                $this->messageManager->addSuccessMessage(__('The entry has been deleted.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
