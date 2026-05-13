<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Model\Webapi;

use Magento\Quote\Api\CartRepositoryInterface;
use Paazl\CheckoutWidget\Api\CheckoutSelection\RepositoryInterface as CheckoutSelectionRepository;
use Paazl\CheckoutWidget\Api\Webapi\MarkPickupOfferedInterface;
use Paazl\CheckoutWidget\Helper\General;
use Paazl\CheckoutWidget\Model\Config;

class MarkPickupOffered implements MarkPickupOfferedInterface
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
    public function execute($cartId, $offered): bool
    {
        $quote = $this->cartRepository->getActive($cartId);
        if (!$this->config->saveCheckoutSelections($quote->getStoreId())) {
            return false;
        }

        try {
            $this->checkoutSelectionRepository->setPickupOffered(
                (int)$quote->getId(),
                (bool)$offered
            );
            return true;
        } catch (\Exception $e) {
            $this->generalHelper->addTolog('MarkPickupOffered', $e->getMessage());
            return false;
        }
    }
}
