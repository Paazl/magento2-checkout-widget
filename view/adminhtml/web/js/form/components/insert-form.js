/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/insert',
    'mageUtils'
], function (Insert, utils) {
    'use strict';

    return Insert.extend({
        defaults: {
            externalFormName: '${ $.ns }.${ $.ns }',
            pageActionsClass: 'page-actions',
            actionsContainerClass: 'page-main-actions',
            exports: {
                prefix: '${ $.externalFormName }:selectorPrefix'
            },
            imports: {
                toolbarSection: '${ $.toolbarContainer }:toolbarSection',
                prefix: '${ $.toolbarContainer }:rootSelector',
                messagesClass: '${ $.externalFormName }:messagesClass'
            },
            settings: {
                ajax: {
                    ajaxSave: true,
                    exports: {
                        ajaxSave: '${ $.externalFormName }:ajaxSave'
                    },
                    imports: {
                        responseStatus: '${ $.externalFormName }:responseStatus',
                        responseData: '${ $.externalFormName }:responseData'
                    }
                }
            },
            modules: {
                externalForm: '${ $.externalFormName }'
            }
        },

        /** @inheritdoc */
        initObservable: function () {
            return this._super()
                .observe('responseStatus');
        },

        /** @inheritdoc */
        initConfig: function (config) {
            var defaults = this.constructor.defaults;

            utils.extend(defaults, defaults.settings[config.formSubmitType] || {});

            return this._super();
        },

        /** @inheritdoc*/
        destroyInserted: function () {
            if (this.isRendered && this.externalForm()) {
                this.externalForm().delegate('destroy');
                this.removeActions();
                this.responseStatus(undefined);
                this.responseData = {};
            }

            return this._super();
        },

        /** @inheritdoc */
        onRender: function (data) {
            if (!data.length) {
                return this;
            }

            this._super(data);
        },

        /**
         * Reset external form data.
         */
        resetForm: function () {
            if (this.externalSource()) {
                this.externalSource().trigger('data.reset');
                this.responseStatus(undefined);
            }
        }
    });
});
