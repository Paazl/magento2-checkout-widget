/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Checkout/js/model/quote'
], function ($, quote) {
    'use strict';

    var storePickupShippingInformation = {
        defaults: {
            template: 'Paazl_CheckoutWidget/shipping-information',
            shippingMethodTitle: '',
        },

        initialize: function () {
            this._super();

            $(document).ajaxComplete((e, xhr, settings) => {
                if (settings.url.includes('shipping-information')) {
                    if (xhr.status === 200) {
                        let response = JSON.parse(xhr.responseText);

                        window.checkoutConfig.totalsData.extension_attributes[0] = {
                            'carrier_title': response.totals.extension_attributes.shipping_methods[0].carrier_title,
                            'method_title': response.totals.extension_attributes.shipping_methods[0].method_title
                        }

                        this.setShippingMethodTitle();
                    }
                }
            });
        },

        initObservable() {
            this._super().observe(['shippingMethodTitle']);
            return this;
        },

        /**
         * Get shipping method title based on delivery method.
         *
         * @return {String}
         */
        getShippingMethodTitle() {
            this.shippingMethodTitle('');

            // Trigger setting shipping method title if quote total subscribe doesn't trigger
            this.setShippingMethodTitle();

            quote.totals.subscribe(() => {
                this.setShippingMethodTitle();
            });
        },

        setShippingMethodTitle() {
            if (window.checkoutConfig.totalsData.extension_attributes[0] && window.checkoutConfig.paazlshipping) {
                const shippingMethod = window.checkoutConfig.totalsData.extension_attributes[0];
                const carrier_title = shippingMethod['carrier_title'];
                const method_title = shippingMethod['method_title'];

                if (carrier_title && method_title) {
                    this.shippingMethodTitle(carrier_title + ' - ' + method_title);
                }
            } else {
                this.getDefaultShippingMethodTitle();
            }
        },

        getDefaultShippingMethodTitle() {
            const shippingMethod = quote.shippingMethod();
            const carrier_title = shippingMethod ? shippingMethod['carrier_title'] : '';
            const method_title = shippingMethod ? shippingMethod['method_title'] : '';

            if (carrier_title && method_title) {
                this.shippingMethodTitle(carrier_title + ' - ' + method_title);
            }
        },

        /**
         * Get is store pickup delivery method selected.
         *
         * @returns {Boolean}
         */
        isStorePickup() {
            var shippingMethod = quote.shippingMethod(),
                isStorePickup = false;

            if (shippingMethod !== null) {
                isStorePickup = shippingMethod['carrier_code'] === 'instore' &&
                    shippingMethod['method_code'] === 'pickup';
            }

            return isStorePickup;
        }
    };

    return function (shippingInformation) {
        return shippingInformation.extend(storePickupShippingInformation);
    };
});
