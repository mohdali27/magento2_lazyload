define([
    './column',
    'jquery',
], function (Column, $) {
    'use strict';
    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html',
            fieldClass: {
                'data-grid-html-cell': true
            }
        },
        getFieldHandler: function (row) {
            return false;
        }
    });
});
