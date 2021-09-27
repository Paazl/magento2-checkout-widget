<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class TotalPrice
 */
class TotalPrice implements ArrayInterface
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
                ['value' => 'subtotal_incl_discount', 'label' => __('Subtotal including DISCOUNT')],
                ['value' => 'subtotal_excl_discount', 'label' => __('Subtotal excluding DISCOUNT')],
                ['value' => 'grand_total', 'label' => __('Grand total')]
            ];
        }

        return $this->options;
    }
}
