<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\ResourceModel\Order;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Paazl\CheckoutWidget\Api\Data\Order\OrderReferenceInterface;

/**
 * Class OrderReference
 *
 * @package Paazl\CheckoutWidget\Model\ResourceModel\Order
 */
class OrderReference extends AbstractDb
{
    public const MAIN_TABLE = 'mm_paazl_order';

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, OrderReferenceInterface::ENTITY_ID);
    }
}
