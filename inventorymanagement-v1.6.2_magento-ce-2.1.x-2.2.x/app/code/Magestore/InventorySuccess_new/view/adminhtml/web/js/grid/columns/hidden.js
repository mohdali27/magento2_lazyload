/*
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'Magento_Ui/js/grid/columns/column'
], function ($, ko, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Magestore_InventorySuccess/grid/columns/inputtext',
            fieldClass: {
                'adjuststock-cell-content': true
            }
        },
        getRowId: function (row) {
            return row[this.index + '_id'];
        },
    });
});
