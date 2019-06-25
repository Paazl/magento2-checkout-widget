<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\ResourceModel\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Paazl\CheckoutWidget\Model\Order\OrderReference;
use Paazl\CheckoutWidget\Model\ResourceModel\Order\OrderReference as OrderReferenceResource;

/**
 * Class OrderReferenceCollection
 *
 * @package Paazl\CheckoutWidget\Model\ResourceModel\Order
 */
class OrderReferenceCollection extends AbstractCollection
{
    /**
     * Init collection and determine table names
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(OrderReference::class, OrderReferenceResource::class);
    }
}
