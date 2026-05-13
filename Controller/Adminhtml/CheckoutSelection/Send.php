<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Controller\Adminhtml\CheckoutSelection;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Paazl\CheckoutWidget\Api\CheckoutSelection\DataInterface;
use Paazl\CheckoutWidget\Model\CheckoutSelection\CollectionFactory;
use Paazl\CheckoutWidget\Service\CheckoutSelections;
use Paazl\CheckoutWidget\Service\SendToPaazl;

class Send extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Paazl_CheckoutWidget::checkout_selection';

    private CollectionFactory $collectionFactory;
    private CheckoutSelections $checkoutSelections;
    private SendToPaazl $sendToPaazl;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        CheckoutSelections $checkoutSelections,
        SendToPaazl $sendToPaazl
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->checkoutSelections = $checkoutSelections;
        $this->sendToPaazl = $sendToPaazl;
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $entityId = (int)$this->getRequest()->getParam('id');

        if ($entityId <= 0) {
            $this->messageManager->addErrorMessage(__('Invalid checkout selection ID.'));
            return $resultRedirect->setPath('*/*/');
        }

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(DataInterface::ENTITY_ID, $entityId);
        $item = $collection->getFirstItem();
        $quoteId = (int)$item->getData(DataInterface::QUOTE_ID);

        if ($quoteId <= 0) {
            $this->messageManager->addErrorMessage(__('Checkout selection not found.'));
            return $resultRedirect->setPath('*/*/');
        }

        if ((int)$item->getData(DataInterface::WAS_SENT) === 1) {
            $this->messageManager->addErrorMessage(__('This selection has already been sent to Paazl.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $selections = $this->checkoutSelections->getByQuoteIds([$quoteId]);
            if (empty($selections)) {
                $this->messageManager->addErrorMessage(__('Nothing to send for this selection.'));
                return $resultRedirect->setPath('*/*/');
            }
            $sent = $this->sendToPaazl->send($selections);
            $this->messageManager->addSuccessMessage(__('Sent %1 selection(s) to Paazl.', $sent));
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(__('Failed to send to Paazl: %1', $e->getMessage()));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
