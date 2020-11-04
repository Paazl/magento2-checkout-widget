/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'uiComponent',
    'jquery',
    'domReady',
    'Magento_Checkout/js/model/shipping-save-processor',
    'Magento_Checkout/js/model/quote'
], function (ko, Component, $, domReady, shippingSaveProcessor, quote) {
    'use strict';

    var shippingConfig = window.checkoutConfig.paazlshipping || {};
    var widgetConfig = shippingConfig.widgetConfig || {};
    var mapActive = false;

    domReady(function () {
        /**
         * Preventing propagation of click event in order to avoid send of Shipping form
         */
        $(document).on('click', "#widget_paazlshipping_paazlshipping button", function (event) {
            event.preventDefault();
            event.stopPropagation();
            return false;
        });
    });

    /**
     * @param {String} url
     * @return {Boolean}
     */
    function isCheckoutUrl(url) {
        return shippingConfig.checkoutApiUrl && (shippingConfig.checkoutApiUrl.indexOf(url) === 0);
    }

    if (shippingConfig.saveShippingInfoInstantly === true) {
        // Updating shipping information at every update of shipping option
        let openOrig = window.XMLHttpRequest.prototype.open;

        window.XMLHttpRequest.prototype.open = function (method, url, async, user, password) {
            if (isCheckoutUrl(url)) {
                this.removeEventListener('load', onLoadEnd);
                this.addEventListener('load', onLoadEnd);
            }

            return openOrig.apply(this, arguments);
        };
    }

    /**
     * @param {ProgressEvent} event
     */
    function onLoadEnd(event) {
        let ready =
            (this.readyState === 4)
            && event.target
            && (event.target.status === 200);

        if (ready) {
            let shippingMethod = quote.shippingMethod();

            if (shippingMethod && shippingMethod['method_code']) {
                shippingSaveProcessor.saveShippingInformation();
            }
        }
    }

    return Component.extend({
        configJson: {},
        customerAddressId: null,
        locked: false,
        state: {
            postcode: null,
            country: null
        },

        initialize: function () {
            this._super();
            this.initWidget();
        },

        initWidget: function () {
            this.configJson = widgetConfig || {};
            this.initMap();
        },

        initMap: function () {
            if ((!mapActive) && shippingConfig.googleMapKey) {
                (function (i, s, o, g, r, a, m) {
                    a = s.createElement(o),
                        m = s.getElementsByTagName(o)[0];
                    a.async = 1;
                    a.src = g;
                    m.parentNode.insertBefore(a, m)
                })(
                    window,
                    document,
                    'script',
                    '//maps.googleapis.com/maps/api/js?key=' + shippingConfig.googleMapKey
                );

                mapActive = true;
            }
        },

        lock: function() {
            this.locked = true;
        },

        unlock: function() {
            this.locked = false;
        },

        /**
         * @return {boolean}
         */
        isLocked: function() {
            return this.locked;
        },

        isHideOtherMethods: function () {
            return shippingConfig.hideOtherMethods;
        },

        getCarrierCode: function () {
            return shippingConfig.carrierCode;
        },

        getMethodCode: function () {
            return shippingConfig.methodCode;
        },

        /**
         *
         * @return {string}
         */
        getJsName: function () {
            var name = 'checkoutjs';
            if (shippingConfig.mode === 'test') {
                name += '_test';
            } else {
                name += '_live';
            }

            return name;
        },

        loadWidget: function (postcode, country) {
            this.initWidget();
            var data = this.configJson,
                self = this;

            if (!data) {
                return;
            }
            var container = $('#' + data.mountElementId);

            if (!container.length) {
                return;
            }

            var infoUpdate = function (paazlCheckout) {
                if (!container.data('paazlactive')) {
                    var postalCode, countryCode;
                    if ((!postcode || !country) && shippingConfig.showOnFirstLoad) {
                        postalCode = data.consigneePostalCode;
                        countryCode = data.consigneeCountryCode;
                    } else if (postcode && country) {
                        postalCode = postcode;
                        countryCode = country;
                    }

                    if (!postalCode || !countryCode) {
                        return;
                    }
                    data.consigneePostalCode = postalCode;
                    data.consigneeCountryCode = countryCode;
                    paazlCheckout.init(data);
                    container.data('paazlactive', true);
                    self.state.postcode = data.consigneePostalCode;
                    self.state.country = data.consigneeCountryCode;
                }
                /*
                 * Don't make an update otherwise:
                 *   change of country/postcode will reload the block with shipping methods.
                 */
            };

            self.customerAddressId = quote.shippingAddress()['customerAddressId'];

            require([this.getJsName()], infoUpdate);
        }
    });
}
);
