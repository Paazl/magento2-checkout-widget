/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/form/form'
], function ($, Form) {
    'use strict';

    return Form.extend({

        /**
         * Process Save In New Attribute Set prompt
         */
        saveAndPush: function () {
            var self = this,
                editForm = self,
                params = {};

            params['push_to_paazl'] = true;
            editForm.setAdditionalData(params);
            editForm.save();
        }
    });
});
