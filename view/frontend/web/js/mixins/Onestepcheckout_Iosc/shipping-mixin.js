/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

define(
    [],
    function () {
        return function (target) {
            return target.extend({
                successHandler: function (data) {

                    if (data.data && data.data.paazlshipping_widgetConfig) {
                        window.checkoutConfig.paazlshipping &&
                        (window.checkoutConfig.paazlshipping.widgetConfig = data.data.paazlshipping_widgetConfig);
                    }

                    this._super(data);
                }
            });
        }
    }
);
