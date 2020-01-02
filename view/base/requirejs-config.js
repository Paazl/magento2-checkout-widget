/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            paazlCheckout: 'Paazl_CheckoutWidget/js/checkout',
            widgetConfig: 'Paazl_CheckoutWidget/js/checkout/view/widget-config',
            paazlShippingModal: 'Paazl_CheckoutWidget/js/admin/order/create/paazl-shipping/modal',
            customJs: 'Paazl_CheckoutWidget/js/custom',
            checkoutjs_test: "https://widget-acc.paazl.com/v1/checkout.js",
            checkoutjs_live: "https://widget.paazl.com/v1/checkout.js"
        }
    },
    paths: {
       paazlCheckout: 'Paazl_CheckoutWidget/js/checkout'
    },
    shim: {
        'Paazl_CheckoutWidget/js/checkout': {
            deps: ['jquery']
        }
    },
};
