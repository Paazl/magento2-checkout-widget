<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class CalculateVolume
 */
class CalculateVolume implements OptionSourceInterface
{

    public const CALCULATE_VOLUME_NO = 0;
    public const CALCULATE_VOLUME_USE_ATTRIBUTE = 1;
    public const CALCULATE_VOLUME_CALCULATE = 2;
    public const CALCULATE_VOLUME_CALCULATE_IF_MISSED = 3;

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
                    'value' => self::CALCULATE_VOLUME_NO,
                    'label' => __('No')
                ],
                [
                    'value' => self::CALCULATE_VOLUME_USE_ATTRIBUTE,
                    'label' => __('Yes, use volume attribute')
                ],
                [
                    'value' => self::CALCULATE_VOLUME_CALCULATE,
                    'label' => __('Yes, always calculate using dimensions')
                ],
                [
                    'value' => self::CALCULATE_VOLUME_CALCULATE_IF_MISSED,
                    'label' => __('Yes, calculate using dimensions if volume missed')
                ]
            ];
        }

        return $this->options;
    }
}
