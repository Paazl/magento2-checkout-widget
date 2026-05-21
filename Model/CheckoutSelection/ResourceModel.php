<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Model\CheckoutSelection;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Paazl\CheckoutWidget\Api\CheckoutSelection\DataInterface;

/**
 * Checkout Selection repository
 */
class ResourceModel extends AbstractDb
{

    /**
     * Table name
     */
    public const ENTITY_TABLE = 'mm_paazl_checkout_selection';

    /**
     * Primary field
     */
    public const PRIMARY = 'entity_id';

    /**
     * @inheritDoc
     */
    protected $_serializableFields = [
        DataInterface::EXT_SHIPPING_INFO => [[], []]
    ];

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(self::ENTITY_TABLE, self::PRIMARY);
    }
}
