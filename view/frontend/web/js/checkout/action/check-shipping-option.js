define(
    [
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'mage/storage',
        'Paazl_CheckoutWidget/js/checkout/view/widget-config'
    ],
    function (stepNavigator, quote, resourceUrl, storage, widgetConfig) {
        return function () {
            var shippingMethod = quote.shippingMethod();
            if (widgetConfig.prototype.getCarrierCode() !== shippingMethod.carrier_code
                || widgetConfig.prototype.getMethodCode() !== shippingMethod.method_code) {
                return;
            }

            var params = (resourceUrl.getCheckoutMethod() == 'guest') ? {quoteId: quote.getQuoteId()} : {},
                urls = {
                    'guest': '/guest-carts/' + quote.getQuoteId() + '/paazl-check-shipping-option',
                    'customer': '/carts/mine/paazl-check-shipping-option'
                },
                url = resourceUrl.getUrl(urls, params);

            storage.get(
                url,
                {},
                false
            ).done(function (response) {
                if (response.reload_widget_config_json) {
                    var newConfig;
                    try {
                        newConfig = JSON.parse(response.reload_widget_config_json);
                    } catch (e) {}
                    widgetConfig.prototype.reloadWidget(newConfig);
                }

                if (response.reload_widget === true) {
                    stepNavigator.navigateTo('shipping', 'opc-shipping_method');
                }

                if (response.totals !== null && response.totals !== undefined) {
                    quote.setTotals(response.totals);
                }
            });
        }
    }
);
