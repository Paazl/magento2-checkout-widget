/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            paazlCheckout: 'Paazl_CheckoutWidget/js/checkout',
            widgetConfig: 'Paazl_CheckoutWidget/js/checkout/view/widget-config',
            'Magento_Checkout/template/shipping-address/shipping-method-item.html': 'Paazl_CheckoutWidget/template/checkout/shipping-method-item.html',
            'Magento_Checkout/template/shipping-address/shipping-method-list.html': 'Paazl_CheckoutWidget/template/checkout/shipping-method-list.html',
            customJs: 'Paazl_CheckoutWidget/js/custom',
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
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                'Paazl_CheckoutWidget/js/checkout/action/set-shipping-information-mixin': true
            },
            'Magento_Checkout/js/model/checkout-data-resolver': {
                'Paazl_CheckoutWidget/js/checkout/model/checkout-data-resolver-mixin': true
            },
            'Magento_Checkout/js/view/summary/abstract-total': {
                'Paazl_CheckoutWidget/js/checkout/view/summary/abstract-total-mixin': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Paazl_CheckoutWidget/js/checkout/view/shipping-mixin': true
            }
        }
    }
};
