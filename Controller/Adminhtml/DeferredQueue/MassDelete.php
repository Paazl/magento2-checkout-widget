<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Controller\Adminhtml\DeferredQueue;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Paazl\CheckoutWidget\Model\ResourceModel\DeferredQueue\DeferredQueue as DeferredQueueResource;
use Paazl\CheckoutWidget\Model\ResourceModel\DeferredQueue\DeferredQueueCollection;
use Paazl\CheckoutWidget\Model\ResourceModel\DeferredQueue\DeferredQueueCollectionFactory;

/**
 * Class MassDelete
 *
 * @package Paazl\CheckoutWidget\Controller\Adminhtml\DeferredQueue
 */
class MassDelete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level
     */
    public const ADMIN_RESOURCE = 'Paazl_CheckoutWidget::deferred_queue';

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var DeferredQueueCollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var DeferredQueueResource
     */
    protected $deferredQueueResource;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param DeferredQueueCollectionFactory $collectionFactory
     * @param DeferredQueueResource $deferredQueueResource
     */
    public function __construct(
        Context $context,
        Filter $filter,
        DeferredQueueCollectionFactory $collectionFactory,
        DeferredQueueResource $deferredQueueResource
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->deferredQueueResource = $deferredQueueResource;
    }

    /**
     * Mass delete action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            /** @var DeferredQueueCollection $collection */
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->getSize();

            foreach ($collection as $item) {
                $this->deferredQueueResource->delete($item);
            }

            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $collectionSize)
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
