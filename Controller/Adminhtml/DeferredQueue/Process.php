<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Controller\Adminhtml\DeferredQueue;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Paazl\CheckoutWidget\Model\DeferredQueue\Processor;

/**
 * Manually transition a single deferred-delivery row to Processing.
 */
class Process extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Paazl_CheckoutWidget::deferred_queue';

    private Processor $processor;

    public function __construct(
        Context $context,
        Processor $processor
    ) {
        parent::__construct($context);
        $this->processor = $processor;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = (int)$this->getRequest()->getParam('id');

        if (!$id) {
            $this->messageManager->addErrorMessage(__('Missing deferred queue entry id.'));
            return $resultRedirect->setPath('*/*/');
        }

        $admin = $this->_auth->getUser();
        $note = sprintf(
            'Order manually transitioned from Deferred Delivery to Processing by %s.',
            $admin ? $admin->getUserName() : 'admin'
        );

        try {
            if ($this->processor->processById($id, $note)) {
                $this->messageManager->addSuccessMessage(
                    __('The deferred order has been moved to Processing.')
                );
            } else {
                $this->messageManager->addNoticeMessage(
                    __('The deferred entry could not be processed (already processed or order no longer in Deferred Delivery).')
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/');
    }
}
