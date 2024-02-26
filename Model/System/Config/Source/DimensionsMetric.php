<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class DimensionsMetric
 */
class DimensionsMetric implements OptionSourceInterface
{

    public const METRIC_MM = 'mm';
    public const METRIC_CM = 'cm';
    public const METRIC_M = 'm';

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
                [
                    'value' => self::METRIC_MM,
                    'label' => __('mm')
                ],
                [
                    'value' => self::METRIC_CM,
                    'label' => __('cm')
                ],
                [
                    'value' => self::METRIC_M,
                    'label' => __('m')
                ]
            ];
        }

        return $this->options;
    }
}
