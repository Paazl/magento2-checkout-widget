<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\ResourceModel\Quote;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Paazl\CheckoutWidget\Api\Data\Quote\QuoteReferenceInterface;
use Paazl\CheckoutWidget\Setup\SetupSchema;

/**
 * Class OrderReference
 *
 * @package Paazl\CheckoutWidget\Model\ResourceModel\Order
 */
class QuoteReference extends AbstractDb
{

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(SetupSchema::TABLE_QUOTE, QuoteReferenceInterface::ENTITY_ID);
    }
}
