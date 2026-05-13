<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Api\Webapi;

interface GuestMarkNextToPaymentInterface
{
    /**
     * Flag the guest cart as having reached the payment step.
     *
     * @param string $cartId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute($cartId): bool;
}
