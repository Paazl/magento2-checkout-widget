<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Api;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Paazl\CheckoutWidget\Api\Data\Order\OrderReferenceInterface;

/**
 * Interface OrderReferenceRepositoryInterface
 *
 * @package Paazl\CheckoutWidget\Api
 */
interface OrderReferenceRepositoryInterface
{

    /**
     * @param int $orderId
     *
     * @return OrderReferenceInterface
     * @throws NoSuchEntityException
     */
    public function getByOrderId($orderId);

    /**
     * @param OrderReferenceInterface $orderReference
     *
     * @return OrderReferenceInterface
     * @throws CouldNotSaveException
     */
    public function save(OrderReferenceInterface $orderReference);
}
