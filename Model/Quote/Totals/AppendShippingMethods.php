<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Model\Quote\Totals;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\TotalsExtensionFactory;
use Magento\Quote\Api\Data\TotalsExtensionInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;

class AppendShippingMethods
{
    /**
     * @var ShippingMethodManagementInterface
     */
    private $shippingMethodManagement;

    /**
     * @var TotalsExtensionFactory
     */
    private $totalsExtensionFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * AppendShippingMethods constructor.
     *
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param TotalsExtensionFactory            $totalsExtensionFactory
     * @param CartRepositoryInterface           $cartRepository
     */
    public function __construct(
        ShippingMethodManagementInterface $shippingMethodManagement,
        TotalsExtensionFactory $totalsExtensionFactory,
        CartRepositoryInterface $cartRepository
    ) {
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->totalsExtensionFactory = $totalsExtensionFactory;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param TotalsInterface $totals
     * @param int             $cartId
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function append(TotalsInterface $totals, $cartId)
    {
        $quote = $this->cartRepository->get($cartId);
        if (!$quote || $quote->getIsVirtual()) {
            return;
        }

        // Adding the list of shipping methods to output
        $methods = $this->shippingMethodManagement->getList($quote->getId());

        /** @var TotalsExtensionInterface|null $extension */
        $extension = $totals->getExtensionAttributes();
        if (!$extension) {
            $extension = $this->totalsExtensionFactory->create();
        }
        $extension->setShippingMethods($methods);
        $totals->setExtensionAttributes($extension);
    }
}
