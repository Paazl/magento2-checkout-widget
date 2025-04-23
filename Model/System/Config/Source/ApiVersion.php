<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class ApiMode
 *
 * @package Paazl\CheckoutWidget\Model\System\Config\Source
 */
class ApiVersion implements ArrayInterface
{

    public const VERSION_STABLE = 'stable';
    public const VERSION_LATEST = 'latest';

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
                ['value' => self::VERSION_STABLE, 'label' => __('Stable')],
                ['value' => self::VERSION_LATEST, 'label' => __('Latest')]
            ];
        }

        return $this->options;
    }
}
