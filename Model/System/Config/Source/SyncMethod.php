<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ApiMode
 *
 * @package Paazl\CheckoutWidget\Model\System\Config\Source
 */
class SyncMethod implements OptionSourceInterface
{

    public const SYNC_METHOD_CRON = 0;
    public const SYNC_METHOD_MANUAL = 1;

    /**
     * @var array
     */
    public $options;

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                ['value' => self::SYNC_METHOD_CRON, 'label' => __('Cron')],
                ['value' => self::SYNC_METHOD_MANUAL, 'label' => __('Manual')]
            ];
        }

        return $this->options;
    }
}
