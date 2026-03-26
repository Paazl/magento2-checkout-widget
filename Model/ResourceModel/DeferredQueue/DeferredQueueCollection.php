<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\ResourceModel\DeferredQueue;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Paazl\CheckoutWidget\Model\DeferredQueue\DeferredQueue as DeferredQueueModel;
use Paazl\CheckoutWidget\Model\ResourceModel\DeferredQueue\DeferredQueue as DeferredQueueResource;

/**
 * Class DeferredQueueCollection
 *
 * @package Paazl\CheckoutWidget\Model\ResourceModel\DeferredQueue
 */
class DeferredQueueCollection extends AbstractCollection
{
    /**
     * Init collection and determine table names
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(DeferredQueueModel::class, DeferredQueueResource::class);
    }

    /**
     * Join with sales_order table to get order increment_id
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
            ['sales_order' => $this->getTable('sales_order')],
            'main_table.order_id = sales_order.entity_id',
            ['increment_id']
        );

        return $this;
    }
}
