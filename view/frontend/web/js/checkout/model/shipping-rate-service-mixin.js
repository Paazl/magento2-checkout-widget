define(
    [
        './shipping-rate-processor/processor-wrapper',
        'mage/utils/wrapper'
    ],
    function (wrapper, utilsWrapper) {
        return function (target) {
            target.registerProcessor = utilsWrapper.wrap(
                target.registerProcessor,
                function (orig, type, processor) {
                    orig(type, wrapper(processor));
                }
            );

            return target;
        };
    }
);