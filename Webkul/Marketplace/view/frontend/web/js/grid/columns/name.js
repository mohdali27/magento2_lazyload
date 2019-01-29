define([
    './column',
    'jquery'
], function (Column, $) {
    'use strict';

    return Column.extend({
        defaults: {
            fieldClass: {
                'wk-mp-grid-name-cell': true
            }
        }
    });
});
