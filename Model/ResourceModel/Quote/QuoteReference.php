<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\ResourceModel\Quote;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Paazl\CheckoutWidget\Api\Data\Quote\QuoteReferenceInterface;

/**
 * Class OrderReference
 *
 * @package Paazl\CheckoutWidget\Model\ResourceModel\Order
 */
class QuoteReference extends AbstractDb
{
    public const MAIN_TABLE = 'mm_paazl_quote';

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, QuoteReferenceInterface::ENTITY_ID);
    }
}
