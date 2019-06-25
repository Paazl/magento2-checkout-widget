/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

define(
    [
        'ko',
        'uiComponent',
        'paazlCheckout',
        'jquery',
        'domReady'
    ],
    function (ko, Component, paazlCheckout, $, domReady) {
        'use strict';
        var shippingConfig = window.checkoutConfig.paazlshipping || {};
        var widgetConfig = shippingConfig.widgetConfig || {};

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

        return Component.extend({
            configJson: ko.observable(),
            widgetLoaded: false,
            state: {
                postcode: null,
                country: null
            },
            initialize: function () {
                this._super();
                this.getConfigJson(widgetConfig);

                if (shippingConfig.googleMapKey) {
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
                }
            },
            getConfigJson: function (data) {
                this.configJson(data);
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
            loadWidget: function (postcode, country) {
                var data = this.configJson();
                if (!data) {
                    return;
                }
                var container = $('#' + data.mountElementId);
                if (!container.length) {
                    return;
                }
                if (!container.data('paazlactive')) {
                    data.consigneePostalCode = postcode || data.consigneePostalCode;
                    data.consigneeCountryCode = country || data.consigneeCountryCode;
                    paazlCheckout.init(data);
                    container.data('paazlactive', true);
                    this.state.postcode = data.consigneePostalCode;
                    this.state.country = data.consigneeCountryCode;
                } else {
                    if (postcode != '' && (postcode != this.state.postcode)) {
                        paazlCheckout.setConsigneePostalCode(postcode);
                        this.state.postcode = postcode;
                    }

                    if (country != '' && (country != this.state.country)) {
                        paazlCheckout.setConsigneeCountryCode(country);
                        this.state.country = country;
                    }
                }
            }
        });
    }
);
