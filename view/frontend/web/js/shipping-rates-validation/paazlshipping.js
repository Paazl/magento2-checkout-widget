/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    '../model/shipping-rates-validator/paazlshipping',
    '../model/shipping-rates-validation-rules/paazlshipping'
], function (
    Component,
    defaultShippingRatesValidator,
    defaultShippingRatesValidationRules,
    flatrateShippingRatesValidator,
    flatrateShippingRatesValidationRules
) {
    'use strict';

    defaultShippingRatesValidator.registerValidator('paazlshipping', flatrateShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('paazlshipping', flatrateShippingRatesValidationRules);

    return Component;
});
