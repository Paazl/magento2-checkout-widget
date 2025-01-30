/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'ko',
    'mage/storage',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-save-processor/payload-extender',
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/error-processor',
    'Paazl_CheckoutWidget/js/checkout/view/widget-config'
], function ($, _, ko, storage, quote, payloadExtender, resourceUrlManager, errorProcessor, widgetConfig) {
    "use strict";

    /**
     * Draws widget if Paazl method is active
     */
    function initWidget()
    {
        if (quote.shippingMethod()
            && (quote.shippingMethod()['method_code'] === 'paazlshipping')
            && quote.shippingAddress()
        ) {
            let shippingAddress = quote.shippingAddress();
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
                return method != null ? method.carrier_code : null;
            }),

            checkAndInitWidget: function () {
                let force = widgetConfig.prototype.customerAddressId !== quote.shippingAddress()['customerAddressId'];
                initWidget();
            },

            /**
             * @returns {boolean}
             */
            isPaazlOnly: function () {
                var rates = this.rates();
                return (rates.length === 1 && rates[0].carrier_code === 'paazlshipping');
            },

            setShippingInformation: function () {
                const savedSelectedShippingAddress = quote.shippingAddress();
                let ajaxHandled = false;
                
                widgetConfig.prototype.lock();
                this._super();

                if (savedSelectedShippingAddress.customerAddressId !== quote.shippingAddress().customerAddressId) {
                    quote.shippingAddress(savedSelectedShippingAddress);

                    $(document).ajaxComplete((_, xhr, settings) => {
                        if (!ajaxHandled && settings.url.includes(resourceUrlManager.getUrlForSetShippingInformation(quote))) {
                            ajaxHandled = true;
                            this.saveShippingInformation();
                        }
                    });
                }

                widgetConfig.prototype.unlock();
            },

            saveShippingInformation: function () {
                var payload;

                payload = {
                    addressInformation: {
                        'shipping_address': quote.shippingAddress(),
                        'billing_address': quote.billingAddress(),
                        'shipping_method_code': quote.shippingMethod()['method_code'],
                        'shipping_carrier_code': quote.shippingMethod()['carrier_code']
                    }
                };
    
                payloadExtender(payload);
    
                return storage.post(
                    resourceUrlManager.getUrlForSetShippingInformation(quote),
                    JSON.stringify(payload)
                ).done(
                    function (response) {
                        quote.setTotals(response.totals);
                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                    }
                );
            }
        });
    }
});
