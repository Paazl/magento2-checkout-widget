/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

define([], function () {
    'use strict';

    let mixin = {
        /**
         * @return {*}
         */
        getShippingMethodTitle: function () {
            let totals = this.totals();

            if (typeof totals['extension_attributes'] !== 'undefined'
                && typeof totals['extension_attributes']['shipping_methods'] !== 'undefined'
                && typeof totals['extension_attributes']['shipping_methods'][0] !== 'undefined'
                && totals['extension_attributes']['shipping_methods'][0]['carrier_code'] === 'paazlshipping'
            ) {
                let shippingMethod = totals['extension_attributes']['shipping_methods'][0];
                if (window.checkoutConfig.totalsData.extension_attributes) {
                    window.checkoutConfig.totalsData.extension_attributes[0]
                        = totals['extension_attributes']['shipping_methods'][0]
                }
                return shippingMethod['carrier_title'] + ' - ' + shippingMethod['method_title'];
            } else if (window.checkoutConfig.totalsData.extension_attributes
                && window.checkoutConfig.totalsData.extension_attributes[0]) {
                let shippingMethod = window.checkoutConfig.totalsData.extension_attributes[0];
                return shippingMethod['carrier_title'] + ' - ' + shippingMethod['method_title'];
            }

            return this._super();
        },
    };

    return function (target) {
        return target.extend(mixin);
    };
});