/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

define(
    [
        'mage/utils/wrapper',
        'Paazl_CheckoutWidget/js/checkout/action/check-shipping-option'
    ],
    function (wrapper, checkShippingOption) {
        return function (target) {

            // Compatibility with 2.4
            if (target.hasOwnProperty('registerSuccessCallback')
                && (typeof target.registerSuccessCallback === 'function')) {
                target.registerSuccessCallback(function (response) {
                    checkShippingOption();
                });

                return target;
            }

            return wrapper.wrap(target, function (orig) {
                return orig().done(function (couponResponse) {
                    if (!couponResponse) {
                        return;
                    }

                    checkShippingOption();
                });
            });
        };
    }
);
