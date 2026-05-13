<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Model\Webapi;

use Magento\Quote\Api\CartRepositoryInterface;
use Paazl\CheckoutWidget\Api\CheckoutSelection\RepositoryInterface as CheckoutSelectionRepository;
use Paazl\CheckoutWidget\Api\Webapi\MarkNextToPaymentInterface;
use Paazl\CheckoutWidget\Helper\General;
use Paazl\CheckoutWidget\Model\Config;

class MarkNextToPayment implements MarkNextToPaymentInterface
{
    private CartRepositoryInterface $cartRepository;
    private CheckoutSelectionRepository $checkoutSelectionRepository;
    private Config $config;
    private General $generalHelper;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        CheckoutSelectionRepository $checkoutSelectionRepository,
        Config $config,
        General $generalHelper
    ) {
        $this->cartRepository = $cartRepository;
        $this->checkoutSelectionRepository = $checkoutSelectionRepository;
        $this->config = $config;
        $this->generalHelper = $generalHelper;
    }

    /**
     * @inheritDoc
     */
    public function execute($cartId): bool
    {
        $quote = $this->cartRepository->getActive($cartId);
        if (!$this->config->saveCheckoutSelections($quote->getStoreId())) {
            return false;
        }

        try {
            $this->checkoutSelectionRepository->markNextToPayment((int)$quote->getId());
            return true;
        } catch (\Exception $e) {
            $this->generalHelper->addTolog('MarkNextToPayment', $e->getMessage());
            return false;
        }
    }
}
