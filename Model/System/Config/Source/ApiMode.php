<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class ApiMode
 *
 * @package Paazl\CheckoutWidget\Model\System\Config\Source
 */
class ApiMode implements ArrayInterface
{
    const MODE_PRODUCTION = 1;
    const MODE_STAGING = 0;

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
                ['value' => '', 'label' => __('Staging')],
                ['value' => '1', 'label' => __('Production')]
            ];
        }

        return $this->options;
    }
}
