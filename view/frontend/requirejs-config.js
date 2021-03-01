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
            'Magento_Checkout/js/model/shipping-rate-service': {
                'Paazl_CheckoutWidget/js/checkout/model/shipping-rate-service-mixin': true
            },
            'Magento_Checkout/js/model/shipping-rate-processor/new-address': {
                'Paazl_CheckoutWidget/js/checkout/model/shipping-rate-processor/processor-mixin': true
            },
            'Magento_Checkout/js/model/shipping-rate-processor/customer-address': {
                'Paazl_CheckoutWidget/js/checkout/model/shipping-rate-processor/processor-mixin': true
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
            },
            'Onestepcheckout_Iosc/js/shipping': {
                'Paazl_CheckoutWidget/js/mixins/Onestepcheckout_Iosc/shipping-mixin': true
            },
            'Magento_SalesRule/js/action/set-coupon-code': {
                'Paazl_CheckoutWidget/js/mixins/Magento_SalesRule/action/set-coupon-code-mixin': true
            },
            'Magento_SalesRule/js/action/cancel-coupon': {
                'Paazl_CheckoutWidget/js/mixins/Magento_SalesRule/action/cancel-coupon-mixin': true
            }
        }
    }
};
