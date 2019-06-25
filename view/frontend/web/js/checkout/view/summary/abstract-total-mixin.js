/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['Magento_Checkout/js/model/step-navigator'], function (stepNavigator) {
    "use strict";

    return function (target) {
        return target.extend({
            /**
             * @return {*}
             */
            isFullMode: function () {
                if (!this.getTotals()) {
                    return false;
                }

                return stepNavigator.isProcessed('shipping');
            }
        });
    }
});
