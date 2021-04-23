<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Api\Webapi;

use Magento\Framework\Exception\NoSuchEntityException;
use Paazl\CheckoutWidget\Api\Data\CheckQuoteResultInterface;

/**
 * Interface CheckShippingOptionInterface
 */
interface GuestCheckShippingOptionInterface
{
    /**
     * @param int $cartId
     * @return CheckQuoteResultInterface
     * @throws NoSuchEntityException
     */
    public function get($cartId);
}
