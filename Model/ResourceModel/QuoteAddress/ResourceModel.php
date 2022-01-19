<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Model\ResourceModel\QuoteAddress;

use Magento\Quote\Model\ResourceModel\Quote\Address;

/**
 * Class Resource
 */
class ResourceModel extends Address
{
    /**
     * @param int $quoteId
     * @return string
     */
    public function getShippingMethodByQuoteId(int $quoteId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('quote_address'),
            'shipping_method'
        )->where('quote_id = :quote_id')
        ->where('address_type = ?', 'shipping');
        $bind = [':quote_id' => $quoteId];
        return $connection->fetchOne($select, $bind);
    }
}
