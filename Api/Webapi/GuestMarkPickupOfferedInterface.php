<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Api\Webapi;

interface GuestMarkPickupOfferedInterface
{
    /**
     * Record whether pickup locations are currently offered for the
     * customer's address on the given guest cart.
     *
     * @param string $cartId
     * @param bool $offered
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute($cartId, $offered): bool;
}
