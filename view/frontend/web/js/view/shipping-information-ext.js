/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/model/quote'
], function (quote) {
    'use strict';

    var storePickupShippingInformation = {
        defaults: {
            template: 'Paazl_CheckoutWidget/shipping-information',
            shippingMethodTitle: '',
        },

        initObservable: function () {
            this._super().observe(['shippingMethodTitle']);
            return this;
        },

        /**
         * Get shipping method title based on delivery method.
         *
         * @return {String}
         */
        getShippingMethodTitle: function () {
            this.shippingMethodTitle('');

            quote.totals.subscribe(() => {
                var shippingMethod = quote.shippingMethod(),
                    shippingMethodTitle = '',
                    locationName = '',
                    title;

                if (window.checkoutConfig.totalsData.extension_attributes[0]) {
                    const carrier_title = shippingMethod['carrier_title'] ? `${shippingMethod['carrier_title']}` : '';
                    const method_title = shippingMethod['method_title'] ? shippingMethod['method_title'] : '';

                    if (typeof shippingMethod['method_title'] !== 'undefined') {
                        shippingMethodTitle = carrier_title + ' - ' + method_title;
                    }

                    shippingMethod = window.checkoutConfig.totalsData.extension_attributes[0];
                    this.shippingMethodTitle(shippingMethodTitle);
                } else {
                    shippingMethod = quote.shippingMethod();

                    if (!this.isStorePickup()) {
                        if (!shippingMethod) return '';

                        shippingMethodTitle = shippingMethod['carrier_title'];

                        if (typeof shippingMethod['method_title'] !== 'undefined') {
                            shippingMethodTitle += ' - ' + shippingMethod['method_title'];
                        }
            
                        return shippingMethodTitle;
                    }

                    title = shippingMethod['carrier_title'] + ' - ' + shippingMethod['method_title'];

                    if (quote.shippingAddress().firstname !== undefined) {
                        locationName = quote.shippingAddress().firstname + ' ' + quote.shippingAddress().lastname;
                        title += ' "' + locationName + '"';
                    }

                    return title;
                }
            });
        },

        /**
         * Get is store pickup delivery method selected.
         *
         * @returns {Boolean}
         */
        isStorePickup: function () {
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
