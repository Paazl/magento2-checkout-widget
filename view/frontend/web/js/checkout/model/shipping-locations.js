define([
    'ko'
], function (ko) {
    'use strict';

    return {
        selectedLocationCode: ko.observable(null),
        locationsList: ko.observable([]),
    }
});
