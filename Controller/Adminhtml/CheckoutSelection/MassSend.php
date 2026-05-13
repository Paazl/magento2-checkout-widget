<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Controller\Adminhtml\CheckoutSelection;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Paazl\CheckoutWidget\Api\CheckoutSelection\DataInterface;
use Paazl\CheckoutWidget\Model\CheckoutSelection\CollectionFactory;
use Paazl\CheckoutWidget\Service\CheckoutSelections;
use Paazl\CheckoutWidget\Service\SendToPaazl;

class MassSend extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Paazl_CheckoutWidget::checkout_selection';

    private Filter $filter;
    private CollectionFactory $collectionFactory;
    private CheckoutSelections $checkoutSelections;
    private SendToPaazl $sendToPaazl;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        CheckoutSelections $checkoutSelections,
        SendToPaazl $sendToPaazl
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->checkoutSelections = $checkoutSelections;
        $this->sendToPaazl = $sendToPaazl;
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collection->addFieldToFilter(DataInterface::WAS_SENT, 0);

            $quoteIds = [];
            foreach ($collection as $item) {
                $quoteIds[] = (int)$item->getData(DataInterface::QUOTE_ID);
            }

            if (empty($quoteIds)) {
                $this->messageManager->addErrorMessage(
                    __('No unsent selections in the current selection.')
                );
                return $resultRedirect->setPath('*/*/');
            }

            $selections = $this->checkoutSelections->getByQuoteIds($quoteIds);
            if (empty($selections)) {
                $this->messageManager->addErrorMessage(__('Nothing to send for the selected rows.'));
                return $resultRedirect->setPath('*/*/');
            }

            $sent = $this->sendToPaazl->send($selections);
            $this->messageManager->addSuccessMessage(__('Sent %1 selection(s) to Paazl.', $sent));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Failed to send to Paazl: %1', $e->getMessage()));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
