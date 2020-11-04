define(
    [
        'widgetConfig',
        'mage/utils/wrapper'
    ],
    function (widgetConfig, wrapper) {
        return function (target) {
            target.getRates = wrapper.wrap(target.getRates, function(orig) {
                if (!widgetConfig.prototype.isLocked()) {
                    return orig();
                }
            });

            return target;
        };
    }
);