<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class FallbackMethod
 *
 * @package Paazl\CheckoutWidget\Model\System\Config\Source
 */
class FallbackMethod implements ArrayInterface
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
                ['value' => '1', 'label' => __('Fallback method 1')],
                ['value' => '2', 'label' => __('Fallback method 2')]
            ];
        }

        return $this->options;
    }
}
