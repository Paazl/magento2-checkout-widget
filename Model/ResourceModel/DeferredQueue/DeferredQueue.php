<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\ResourceModel\DeferredQueue;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Paazl\CheckoutWidget\Api\Data\DeferredQueue\DeferredQueueInterface;

/**
 * Class DeferredQueue
 *
 * @package Paazl\CheckoutWidget\Model\ResourceModel\DeferredQueue
 */
class DeferredQueue extends AbstractDb
{
    public const MAIN_TABLE = 'mm_paazl_deferred_queue';

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, DeferredQueueInterface::ENTITY_ID);
    }
}
