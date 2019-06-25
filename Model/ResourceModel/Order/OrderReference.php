<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\ResourceModel\Order;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Paazl\CheckoutWidget\Api\Data\Order\OrderReferenceInterface;
use Paazl\CheckoutWidget\Setup\SetupSchema;

/**
 * Class OrderReference
 *
 * @package Paazl\CheckoutWidget\Model\ResourceModel\Order
 */
class OrderReference extends AbstractDb
{

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(SetupSchema::TABLE_ORDER, OrderReferenceInterface::ENTITY_ID);
    }
}
