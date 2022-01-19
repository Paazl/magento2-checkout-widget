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
            checkoutjs_local_test: "Paazl_CheckoutWidget/js/checkout/lib/checkout_local_test",
            checkoutjs_local_live: "Paazl_CheckoutWidget/js/checkout/lib/checkout_local_live"
        }
    },
    paths: {
       paazlCheckout: 'Paazl_CheckoutWidget/js/checkout',
        checkoutjs_test: [
            "https://widget-acc.paazl.com/v1/checkout",
            "Paazl_CheckoutWidget/js/checkout/lib/checkout_local_test"
        ],
        checkoutjs_live: [
            "https://widget.paazl.com/v1/checkout",
            "Paazl_CheckoutWidget/js/checkout/lib/checkout_local_live"
        ],
    },
    shim: {
        'Paazl_CheckoutWidget/js/checkout': {
            deps: ['jquery']
        }
    },
};
