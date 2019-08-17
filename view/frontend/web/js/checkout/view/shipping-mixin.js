/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'ko',
    'Magento_Checkout/js/model/quote',
    'Paazl_CheckoutWidget/js/checkout/view/widget-config'
], function (
    $,
    _,
    ko,
    quote,
    widgetConfig
) {
    "use strict";

    /**
     * Draws widget if Paazl method is active
     */
    function initWidget()
    {
        if (quote.shippingMethod() && quote.shippingAddress() && (quote.shippingMethod()['method_code'] === 'paazlshipping')) {
            var shippingAddress = quote.shippingAddress();
            widgetConfig.prototype.loadWidget(shippingAddress.postcode, shippingAddress.countryId);
        }
    }

    return function (target) {
        return target.extend({
            defaults: {
                shippingMethodListTemplate: 'Paazl_CheckoutWidget/checkout/shipping-method-list',
                shippingMethodItemTemplate: 'Paazl_CheckoutWidget/checkout/shipping-method-item'
            },

            /**
             * @return {*}
             */
            isSelected: ko.computed(function () {
                if (quote.shippingMethod()) {
                    var infoblock = 'infotext_' + quote.shippingMethod()['method_code'] + '_' + quote.shippingMethod()['carrier_code'];
                    $('#' + infoblock).css('display', 'block');
                    initWidget();
                }

                var selected = null;
                if (quote.shippingMethod()) {
                    selected = quote.shippingMethod()['carrier_code'] + '_' + quote.shippingMethod()['method_code'];
                }
                return selected;
            }),

            /**
             * @return {Boolean}
             */
            canShowPaazlWidget: ko.computed(function () {
                var method = quote.shippingMethod();
                return method != null ? method.carrier_code: null;
            }),

            checkAndInitWidget: function () {
                initWidget();
            },

            /**
             * @returns {boolean}
             */
            isPaazlOnly: function () {
                var rates = this.rates();
                return (rates.length === 1 && rates[0].carrier_code === 'paazlshipping');
            }
        });
    }
});
