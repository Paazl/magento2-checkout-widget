<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Observer\Admin\Order\Create;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\AdminOrder\Create as OrderCreateModel;
use Magento\Quote\Model\Quote;
use Paazl\CheckoutWidget\Model\Api\Builder\Reference;
use Paazl\CheckoutWidget\Model\Api\Processor\CheckoutInfoToQuote;
use Paazl\CheckoutWidget\Model\Carrier\Paazlshipping;

class ProcessData implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var CheckoutInfoToQuote
     */
    private $checkoutInfoToQuote;

    /**
     * @var Reference
     */
    private $referenceBuilder;

    /**
     * ProcessData constructor
     *
     * @param ManagerInterface $messageManager
     * @param CheckoutInfoToQuote $checkoutInfoToQuote
     * @param Reference $referenceBuilder
     */
    public function __construct(
        ManagerInterface $messageManager,
        CheckoutInfoToQuote $checkoutInfoToQuote,
        Reference $referenceBuilder
    ) {
        $this->messageManager = $messageManager;
        $this->checkoutInfoToQuote = $checkoutInfoToQuote;
        $this->referenceBuilder = $referenceBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        /** @var OrderCreateModel $orderCreateModel */
        $orderCreateModel = $observer->getOrderCreateModel();
        $shippingMethod = $orderCreateModel->getShippingAddress()->getShippingMethod();
        if ($shippingMethod === Paazlshipping::CODE . '_' . Paazlshipping::CODE) {
            /** @var Quote $quote */
            $quote = $orderCreateModel->getQuote();
            $this->checkoutInfoToQuote->process($quote);
            $quote->getShippingAddress()->setCollectShippingRates(1);
        }
    }
}
