/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'uiComponent',
    'jquery',
    'domReady'
], function (ko, Component, $, domReady) {
    'use strict';

    var shippingConfig,
        widgetConfig;

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
        customerAddressId: null,
        state: {
            postcode: null,
            country: null,
            token: null
        },

        initialize: function () {
            this._super();
            this.initConfig();
        },

        initConfig: function () {
            shippingConfig = order.paazlshipping || {};
            widgetConfig = shippingConfig.widgetConfig || {};

            this.setConfigJson(widgetConfig);
        },

        setConfigJson: function (data) {
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
            this.initialize();

            var data = this.configJson(),
                self = this;

            if (!data) {
                return;
            }
            var container = $('#' + data.mountElementId);

            if (!container.length) {
                return;
            }

            container.html('');

            var infoUpdate = function (paazlCheckout) {
                data.consigneePostalCode = postcode || data.consigneePostalCode;
                data.consigneeCountryCode = country || data.consigneeCountryCode;
                paazlCheckout.init(data);
                self.state.postcode = data.consigneePostalCode;
                self.state.country = data.consigneeCountryCode;
                self.state.token = data.token;
            };

            require([this.getJsName()], infoUpdate);
        }
    });
});
