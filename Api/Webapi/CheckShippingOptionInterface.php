<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Api\Webapi;

/**
 * Interface CheckShippingOptionInterface
 */
interface CheckShippingOptionInterface
{
    /**
     * @param string $cartId
     * @return \Paazl\CheckoutWidget\Api\Data\CheckQuoteResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($cartId);
}
