<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class AvailableTabs
 *
 * @package Paazl\CheckoutWidget\Model\System\Config\Source
 */
class AvailableTabs implements ArrayInterface
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
                ['value' => 'DELIVERY', 'label' => __('Delivery')],
                ['value' => 'STORE', 'label' => __('Store')],
                ['value' => 'PICKUP', 'label' => __('Pickup')]
            ];
        }

        return $this->options;
    }
}
