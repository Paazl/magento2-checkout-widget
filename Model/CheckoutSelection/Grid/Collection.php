<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Model\CheckoutSelection\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Paazl\CheckoutWidget\Model\CheckoutSelection\ResourceModel;
use Psr\Log\LoggerInterface as Logger;

class Collection extends SearchResult
{
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = ResourceModel::ENTITY_TABLE,
        $resourceModel = ResourceModel::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
            ['sales_order' => $this->getTable('sales_order')],
            'main_table.quote_id = sales_order.quote_id',
            ['increment_id', 'store_id']
        )->joinLeft(
            ['quote' => $this->getTable('quote')],
            'main_table.quote_id = quote.entity_id',
            ['quote_updated_at' => 'updated_at', 'customer_email']
        );

        // sales_order and quote both expose entity_id / created_at / updated_at,
        // so disambiguate the columns that can be filtered or sorted from the grid.
        $this->addFilterToMap('entity_id', 'main_table.entity_id');
        $this->addFilterToMap('quote_id', 'main_table.quote_id');
        $this->addFilterToMap('created_at', 'main_table.created_at');
        $this->addFilterToMap('updated_at', 'main_table.updated_at');
        $this->addFilterToMap('quote_updated_at', 'quote.updated_at');

        return $this;
    }
}
