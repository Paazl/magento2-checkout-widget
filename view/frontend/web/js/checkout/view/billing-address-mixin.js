define([
    'underscore',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer-addresses',
    'Magento_Checkout/js/checkout-data',
    'Paazl_CheckoutWidget/js/checkout/model/shipping-locations',
    'widgetConfig',
    'Magento_Checkout/js/action/create-billing-address',
    'Magento_Checkout/js/action/select-billing-address'
], function (_, quote, addressList, checkoutData, shippingLocations, widgetConfig, createBillingAddress, selectBillingAddress) {

    'use strict';

    return function (billingAddress) {
        return billingAddress.extend({
            useShippingAddress: function () {
                var shippingMethod = quote.shippingMethod(),
                    shippingAddress = quote.shippingAddress();
                if (this.isAddressSameAsShipping() &&
                    shippingLocations.selectedLocationCode()
                    && shippingLocations.locationsList().length
                    && shippingMethod
                    && widgetConfig.prototype.getCarrierCode() === shippingMethod.carrier_code
                    && widgetConfig.prototype.getMethodCode() === shippingMethod.method_code) {
                    if (window.isCustomerLoggedIn && shippingAddress.customerAddressId) {
                        _.each(addressList.getAddressItems(), function (address) {
                            if (shippingAddress.getKey() === address.getKey()) {
                                selectBillingAddress(address);
                            }
                        });
                        checkoutData.setSelectedBillingAddress(null);
                        return true;
                    } else {
                        var shippingAddressFromData = checkoutData.getShippingAddressFromData();
                        if (shippingAddressFromData) {
                            var newBillingAddress = createBillingAddress(shippingAddressFromData);
                            newBillingAddress.getCacheKey = function () {
                                return shippingAddress.getCacheKey();
                            }
                            selectBillingAddress(newBillingAddress);
                            checkoutData.setSelectedBillingAddress(null);
                            return true;
                        }
                    }
                }

                return this._super();
            }
        });
    };
});
