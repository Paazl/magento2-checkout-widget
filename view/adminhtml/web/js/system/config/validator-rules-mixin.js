/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['jquery'], function ($) {
    'use strict';

    return function (target) {
        $.validator.addMethod(
            'validate-paazl-delivery-matrix-code',
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[A-Z]{1,2}$/.test(v);
            },
            $.mage.__('Please enter a valid value in this field (from "A" to "Z", followed by "AA" to "ZZ").')
        );

        return target;
    };
});