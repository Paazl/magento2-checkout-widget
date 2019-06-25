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
     * @param              $contentType
     *
     * @return array
     */
    public function afterGetExcludes(Minification $subject, array $result, $contentType)
    {
        if ($contentType == 'js') {
            $result[] = 'Paazl_CheckoutWidget/js/checkout';
        }

        return $result;
    }
}
