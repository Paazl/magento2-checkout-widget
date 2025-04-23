<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Plugin;

use Magento\Framework\View\Asset\Minification;

/**
 * Class ExcludeFromMinification
 *
 * @package Paazl\CheckoutWidget\Plugin
 */
class ExcludeFromMinification
{

    /**
     * Exclude checkout.js from minification
     *
     * @param Minification $subject
     * @param array        $result
     * @param string       $contentType
     *
     * @return array
     */
    public function afterGetExcludes(Minification $subject, array $result, $contentType)
    {
        if ($contentType == 'js') {
            $result[] = 'Paazl_CheckoutWidget/js/checkout';
            $result[] = 'https://widget-acc.paazl.com/v1/checkout.js';
            $result[] = 'https://widget.paazl.com/v1/checkout.js';
            $result[] = 'https://widget-acc.paazl.com/v1/stable/checkout.js';
            $result[] = 'https://widget-acc.paazl.com/v1/latest/checkout.js';
            $result[] = 'https://widget.paazl.com/v1/stable/checkout.js';
            $result[] = 'https://widget.paazl.com/v1/latest/checkout.js';
        }

        return $result;
    }
}
