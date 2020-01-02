/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Paazl_CheckoutWidget/js/admin/order/create/shipping/method/paazl/widget/config',
    'jquery/ui',
    'Magento_Ui/js/modal/modal'
], function($, widgetConfig) {
    "use strict";

    $.widget('mage.paazlWidgetModal', $.mage.modal, {
        options: {
            modalForm: '#paazl-modal',
            modalButton: '.paazl-modal-open'
        },
        _create: function () {
            Object.assign(this.options, this._prepareModal());
            this._super();
        },
        _prepareModal: function () {
            return {
                responsive: true,
                clickableOverlay: false,
                title: $.mage.__('Choice shipping method'),
                modalClass: 'popup paazl-widget-modal',
                buttons: [{
                    text: $.mage.__('Continue'),
                    class: '',
                    click: function () {
                        this.saveShippingMethod();
                    }
                }]
            };
        },
        openModal: function () {
            this._super();

            let addressContainer = order.shippingAsBilling ?
                    'billingAddressContainer' :
                    'shippingAddressContainer',
                addressIndex = order.shippingAsBilling ?
                    'billing_address' :
                    'shipping_address',
                data = order.serializeData(order[addressContainer]).toObject(),
                postcode = data['order[' + addressIndex + '][postcode]'],
                countryId = data['order[' + addressIndex + '][country_id]'];

            widgetConfig.prototype.loadWidget(postcode, countryId);
        },
        closeModal: function (force = false) {
            this._super();

            if (force) {
                this._super();
                return;
            }

            if (!order.oldShippingMethod) {
                order.resetShippingMethod();
                return;
            }

            order.showModalFlag = false;
            order.setShippingMethod(order.oldShippingMethod);
        },
        saveShippingMethod: function () {
            order.showModalFlag = false;
            order.setShippingMethod('paazlshipping_paazlshipping');
            this.closeModal(true);
        }
    });

    return $.mage.paazlWidgetModal;
});