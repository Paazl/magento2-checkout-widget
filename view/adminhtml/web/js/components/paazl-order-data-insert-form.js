/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Paazl_CheckoutWidget/js/form/components/insert-form'
], function (InsertForm) {
    'use strict';

    return InsertForm.extend({
        defaults: {
            modules: {
                modalComponent: '${ $.modalComponent }'
            },
            listens: {
                responseStatus: 'processResponseStatus'
            }
        },

        /**
         * Process response status.
         */
        processResponseStatus: function () {
            this.modalComponent().closeModal();
            window.location.reload();
        }
    });
});
