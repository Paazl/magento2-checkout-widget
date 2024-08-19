/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Updates current quote's shipping method with data from server.
 *      Reason: after shipping option is selected,
 *              JS object of the quote still contains old "method_title"
 */
define([
    'underscore',
    'Magento_Checkout/js/model/quote',
    'widgetConfig',
    'mage/utils/wrapper',
    'Paazl_CheckoutWidget/js/checkout/model/shipping-locations',
    'Magento_Customer/js/customer-data'
], function (_, quote, widgetConfig, wrapper, shippingLocations, customerData) {
    'use strict';

    return function (target) {
        return wrapper.wrap(target, function (originalAction) {
            let shippingMethod = quote.shippingMethod();
            let address = getActiveAddress();
            
            if (shippingLocations.selectedLocationCode()
                && shippingLocations.locationsList().length
                && shippingMethod
                && widgetConfig.prototype.getCarrierCode() === shippingMethod.carrier_code
                && widgetConfig.prototype.getMethodCode() === shippingMethod.method_code) 
            {
                var collectionPointInfo =_.findWhere(shippingLocations.locationsList(), {code: shippingLocations.selectedLocationCode()}),
                    shippingAddress = quote.shippingAddress();

                if (collectionPointInfo && collectionPointInfo.address) {
                    shippingAddress.countryId = collectionPointInfo.address.country;
                    shippingAddress.city = collectionPointInfo.address.city;
                    shippingAddress.postcode = collectionPointInfo.address.postalCode;
                    shippingAddress.street = [
                        collectionPointInfo.address.street,
                        collectionPointInfo.address.streetNumber
                    ];
                    quote.shippingAddress(shippingAddress);
                }
            } else {
                quote.shippingAddress(address);
            }

            // Update billing address after updating shipping address
            quote.billingAddress(address);

            function getActiveAddress() {
                const isCustomerLogin = window.checkoutConfig.isCustomerLoggedIn;
                const selectedShippingAddress = customerData.get('checkout-data')().selectedShippingAddress;
                const shippingAddress = quote.billingAddress();
                let currentAddress;
                    
                if (isCustomerLogin) {
                    currentAddress = selectedShippingAddress === 'new-customer-address'
                        ? customerData.get('checkout-data')().shippingAddressFromData
                        : window.checkoutConfig.customerData.addresses[getAddressId()];
                } else {
                    currentAddress = customerData.get('checkout-data')().shippingAddressFromData;
                }

                shippingAddress.countryId = currentAddress.country_id;
                shippingAddress.city = currentAddress.city;
                shippingAddress.postcode = currentAddress.postcode;
                shippingAddress.street = Object.values(currentAddress.street);

                return shippingAddress;
            }

            function getAddressId() {
                const selectedShippingAddress = customerData.get('checkout-data')().selectedShippingAddress;

                if (selectedShippingAddress) {
                    return selectedShippingAddress?.match(/\d+/)[0];
                }

                return window.checkoutConfig.customerData.default_billing;
            }

            return originalAction().done(function (res) {
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
        });
    }
});
