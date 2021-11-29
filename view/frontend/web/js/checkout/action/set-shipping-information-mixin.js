/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Updates current quote's shipping method with data from server.
 *      Reason: after shipping option is selected,
 *              JS object of the quote still contains old "method_title"
 */
define(
    [
        'underscore',
        'Magento_Checkout/js/model/quote',
        'widgetConfig'
    ],
    function (
        _,
        quote,
        widgetConfig
    ) {
        return function (target) {
            return function () {
                return target().done(function (res) {
                    var shippingMethod = quote.shippingMethod();
                    if (widgetConfig.prototype.getCarrierCode() !== shippingMethod.carrier_code
                        || widgetConfig.prototype.getMethodCode() !== shippingMethod.method_code) {
                        return;
                    }
                    var methods = [];
                    if ((typeof res.totals !== 'undefined')
                        && (typeof res.totals.extension_attributes !== 'undefined')) {
                        methods = res.totals.extension_attributes || [];
                    } else if (typeof res.extension_attributes !== 'undefined') {
                        methods = res.extension_attributes.shipping_methods || [];
                    }
                    var found = _.find(methods, function (m) {
                        return m.carrier_code === shippingMethod.carrier_code
                            && m.method_code === shippingMethod.method_code;
                    });

                    found && quote.shippingMethod(found);
                })
            }
        }
    }
);
