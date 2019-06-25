<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class WidgetTheme
 *
 * @package Paazl\CheckoutWidget\Model\System\Config\Source
 */
class WidgetTheme implements ArrayInterface
{

    /**
     * @var array
     */
    public $options;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                ['value' => 'DEFAULT', 'label' => __('Default')],
                ['value' => 'GREEN', 'label' => __('Green')],
                ['value' => 'LIGHT-GREEN', 'label' => __('Light Green')],
                ['value' => 'BROWN', 'label' => __('Brown')],
                ['value' => 'BLUE', 'label' => __('Blue')],
                ['value' => 'RED', 'label' => __('Red')],
                ['value' => 'CUSTOM', 'label' => __('Custom')],
            ];
        }

        return $this->options;
    }
}
