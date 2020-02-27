/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            paazlWidgetModal: 'Paazl_CheckoutWidget/js/admin/order/create/shipping/method/paazl/widget/modal'
        }
    },
    config: {
        mixins: {
            'mage/validation': {
                'Paazl_CheckoutWidget/js/system/config/validator-rules-mixin': true
            }
        }
    }
};
