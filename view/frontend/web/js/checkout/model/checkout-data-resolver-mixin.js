/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/checkout-data',
    'widgetConfig'
], function (
    $,
    _,
    quote,
    selectShippingMethodAction,
    checkoutData,
    widgetConfig
) {
    "use strict";

    /**
     * @param {Array} rates
     * @return {*}
     */
    function findPaazlMethod(rates)
    {
        return _.find(rates, function (rate) {
            return rate.carrier_code === 'paazlshipping';
        });
    }

    var mixin = {
        /**
         * @param {Object} ratesData
         */
        resolveShippingRates: function (ratesData) {
            var selectedShippingRate = checkoutData.getSelectedShippingRate(),
                availableRate = false;

            var shippingMethodTitle = $('.checkout-shipping-method .step-title');
            if (ratesData.length === 1) {
                selectShippingMethodAction(ratesData[0]);

                if (findPaazlMethod(ratesData)) {
                    // We have Paazl only - hiding the section's header
                    shippingMethodTitle.hide();
                }

                return;
            }
            shippingMethodTitle.show();

            if (quote.shippingMethod()) {
                availableRate = _.find(ratesData, function (rate) {
                    return rate['carrier_code'] == quote.shippingMethod()['carrier_code'] && //eslint-disable-line
                        rate['method_code'] == quote.shippingMethod()['method_code']; //eslint-disable-line eqeqeq
                });
            }

            if (!availableRate && selectedShippingRate) {
                availableRate = _.find(ratesData, function (rate) {
                    return rate['carrier_code'] + '_' + rate['method_code'] === selectedShippingRate;
                });
            }

            if (!availableRate && window.checkoutConfig.selectedShippingMethod) {
                availableRate = window.checkoutConfig.selectedShippingMethod;
                selectShippingMethodAction(window.checkoutConfig.selectedShippingMethod);

                return;
            }

            if (!availableRate) {
                // Still cannot find the rate - looking for Paazl, selecting it by default
                availableRate = findPaazlMethod(ratesData) || false;
            }

            //Unset selected shipping method if not available
            if (!availableRate) {
                selectShippingMethodAction(null);
            } else {
                selectShippingMethodAction(availableRate);
            }
        },
    };

    return function (target) {
        return _.extend(target, mixin);
    }
});
