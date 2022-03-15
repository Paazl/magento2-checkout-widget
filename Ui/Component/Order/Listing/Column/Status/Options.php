<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Ui\Component\Order\Listing\Column\Status;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status Options
 */
class Options implements OptionSourceInterface
{
    public const VALUE_NOT_PAAZL = '0';
    public const LABEL_NOT_PAAZL = 'Not Paazl';
    public const VALUE_NEED_TO_BE_UPDATED = '1';
    public const LABEL_NEED_TO_BE_UPDATED = 'Need to be updated to Paazl';
    public const VALUE_SUCCESSFULLY_UPDATED = '2';
    public const LABEL_SUCCESSFULLY_UPDATED = 'Successfully Updated';

    /**
     * @var array
     */
    protected $options;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $statuses = $this->getAvailableStatuses();
        foreach ($statuses as $statusCode => $statusName) {
            $this->options[$statusCode]['label'] = $statusName;
            $this->options[$statusCode]['value'] = $statusCode;
        }

        return $this->options;
    }

    /**
     * Retrieves available statuses
     *
     * @return array
     */
    private function getAvailableStatuses()
    {
        // @codingStandardsIgnoreStart
        return [
            self::VALUE_NOT_PAAZL => __(self::LABEL_NOT_PAAZL),
            self::VALUE_NEED_TO_BE_UPDATED => __(self::LABEL_NEED_TO_BE_UPDATED),
            self::VALUE_SUCCESSFULLY_UPDATED => __(self::LABEL_SUCCESSFULLY_UPDATED)
        ];
        // @codingStandardsIgnoreEnd
    }
}
