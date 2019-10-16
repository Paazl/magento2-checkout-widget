<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\ResourceModel\Order\Grid;

use Zend_Db_Expr;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OriginalCollection;
use Paazl\CheckoutWidget\Model\ResourceModel\Order\OrderReference;
use Paazl\CheckoutWidget\Ui\Component\Order\Listing\Column\Status\Options;

/**
 * Extended Order Grid Collection
 */
class Collection extends OriginalCollection
{
    /**
     * Join Paazl Statuses
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->getSelect()->joinLeft(
            ['mpo' => $this->getTable(OrderReference::MAIN_TABLE)],
            'mpo.order_id = main_table.entity_id',
            [
                'paazl_status' => $this->getSelect()->getConnection()->getCheckSql(
                    'mpo.entity_id IS NOT NULL',
                    $this->getSelect()->getConnection()->getCheckSql(
                        'mpo.ext_sent_at IS NOT NULL',
                        Options::VALUE_SUCCESSFULLY_UPDATED,
                        Options::VALUE_NEED_TO_BE_UPDATED
                    ),
                    Options::VALUE_NOT_PAAZL
                )
            ]
        );

        parent::_renderFiltersBefore();
    }

    /**
     * {@inheritdoc}
     */
    public function getSelectCountSql()
    {
        $select = parent::getSelectCountSql();
        $select->columns(
            [
                'paazl_status' => new Zend_Db_Expr($this->getSelect()->getConnection()->getCheckSql(
                    'mpo.entity_id IS NOT NULL',
                    $this->getSelect()->getConnection()->getCheckSql(
                        'mpo.ext_sent_at IS NOT NULL',
                        Options::VALUE_SUCCESSFULLY_UPDATED,
                        Options::VALUE_NEED_TO_BE_UPDATED
                    ),
                    Options::VALUE_NOT_PAAZL
                ))
            ]
        );
        $select->group('paazl_status');

        return $select;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'paazl_status') {
            $this->getSelect()->having('paazl_status = ?', $condition);
            return $this;
        }
        return parent::addFieldToFilter($field, $condition);
    }
}
