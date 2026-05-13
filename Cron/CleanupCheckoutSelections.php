<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Cron;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Paazl\CheckoutWidget\Helper\General as GeneralHelper;
use Paazl\CheckoutWidget\Model\CheckoutSelection\ResourceModel as CheckoutSelectionResource;
use Paazl\CheckoutWidget\Model\Config;

class CleanupCheckoutSelections
{

    private ResourceConnection $resource;
    private GeneralHelper $generalHelper;
    private Config $config;
    private DateTime $dateTime;

    public function __construct(
        ResourceConnection $resource,
        GeneralHelper $generalHelper,
        Config $config,
        DateTime $dateTime
    ) {
        $this->resource = $resource;
        $this->generalHelper = $generalHelper;
        $this->config = $config;
        $this->dateTime = $dateTime;
    }

    public function execute(): void
    {
        if (!$this->config->isCheckoutSelectionsCleanupEnabled()) {
            return;
        }

        $days = $this->config->getCheckoutSelectionsCleanupDays();
        $threshold = date('Y-m-d H:i:s', $this->dateTime->gmtTimestamp() - ($days * 86400));

        try {
            $connection = $this->resource->getConnection();
            $table = $this->resource->getTableName(CheckoutSelectionResource::ENTITY_TABLE);
            $deleted = $connection->delete(
                $table,
                ['was_sent = ?' => 1, 'created_at < ?' => $threshold]
            );

            if ($deleted > 0) {
                $this->generalHelper->addTolog('CleanupCheckoutSelections', sprintf(
                    'Deleted %d sent checkout selection(s) older than %s',
                    $deleted,
                    $threshold
                ));
            }
        } catch (Exception $e) {
            $this->generalHelper->addTolog('CleanupCheckoutSelections', $e->getMessage());
        }
    }
}
