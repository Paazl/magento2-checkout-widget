/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
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
            },
            'Magento_Checkout/js/view/summary/shipping': {
                'Paazl_CheckoutWidget/js/mixins/Magento_Checkout/view/summary/shipping': true
            },
            'Kega_Checkout/js/view/summary/shipping-mixin': {
                'Paazl_CheckoutWidget/js/mixins/Magento_Checkout/view/summary/shipping': true
            }
        }
    }
};
