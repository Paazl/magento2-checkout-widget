/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

define([], function () {
    'use strict';

    return {
        /**
         * @return {Object}
         */
        getRules: function () {
            return {
                'postcode': {
                    'required': true
                },
                'country_id': {
                    'required': true
                },
                'street.0': {
                    'required': true
                }
            };
        }
    };
});
