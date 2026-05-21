<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Paazl\CheckoutWidget\Api\CheckoutSelection\RepositoryInterface as CheckoutSelectionRepository;
use Paazl\CheckoutWidget\Helper\General;
use Paazl\CheckoutWidget\Model\Config;

class MarkCheckoutSelectionFinal implements ObserverInterface
{
    private CheckoutSelectionRepository $checkoutSelectionRepository;
    private Config $config;
    private General $generalHelper;

    public function __construct(
        CheckoutSelectionRepository $checkoutSelectionRepository,
        Config $config,
        General $generalHelper
    ) {
        $this->checkoutSelectionRepository = $checkoutSelectionRepository;
        $this->config = $config;
        $this->generalHelper = $generalHelper;
    }

    public function execute(Observer $observer): void
    {
        /** @var OrderInterface $order */
        $order = $observer->getEvent()->getOrder();
        if (!$order || !$order->getQuoteId()) {
            return;
        }
        if (!$this->config->saveCheckoutSelections($order->getStoreId())) {
            return;
        }

        try {
            $this->checkoutSelectionRepository->markAsFinal((int)$order->getQuoteId());
        } catch (\Throwable $e) {
            $this->generalHelper->addTolog('MarkCheckoutSelectionFinal', $e->getMessage());
        }
    }
}
