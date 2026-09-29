<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Test\Unit\Cron;

use Magento\Framework\DB\Select;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Paazl\CheckoutWidget\Cron\SendOrders;
use Paazl\CheckoutWidget\Helper\General as GeneralHelper;
use Paazl\CheckoutWidget\Model\Api\Processor\SendToService;
use Paazl\CheckoutWidget\Model\Config;
use Paazl\CheckoutWidget\Model\System\Config\Source\SyncMethod;
use Paazl\CheckoutWidget\Test\Unit\UnitTestCase;

class SendOrdersTest extends UnitTestCase
{
    public function testRetryWindowIsMeasuredFromOrderCreation()
    {
        $conditions = [];
        $select = $this->createStub(Select::class);
        $select->method('joinInner')->willReturnSelf();
        $select->method('where')->willReturnCallback(
            static function ($condition) use (&$conditions, $select) {
                $conditions[] = (string)$condition;
                return $select;
            }
        );

        $collection = $this->createStub(Collection::class);
        $collection->method('getSelect')->willReturn($select);
        $collection->method('getTable')->willReturnArgument(0);
        $collection->method('getIterator')->willReturn(new \ArrayIterator([]));

        $collectionFactory = $this->createStub(CollectionFactory::class);
        $collectionFactory->method('create')->willReturn($collection);

        $config = $this->createStub(Config::class);
        $config->method('getSyncMethod')->willReturn(SyncMethod::SYNC_METHOD_CRON);

        $cron = new SendOrders(
            $this->createStub(SendToService::class),
            $collectionFactory,
            $this->createStub(GeneralHelper::class),
            $config
        );
        $cron->execute();

        $window = implode(' ', $conditions);
        $this->assertStringContainsString('`main_table`.`created_at`', $window);
        $this->assertStringNotContainsString('updated_at', $window);
    }
}
