define(
    [
        './processor-wrapper'
    ],
    function (wrapper) {
        return function (target) {
            return wrapper(target);
        };
    }
);