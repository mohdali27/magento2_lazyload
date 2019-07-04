/*
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'Magento_Ui/js/grid/columns/column'
], function ($, ko, UISelect) {
    'use strict';

    return UISelect.extend({
        getLabel: function (record) {
            return record[this.index] * 1;
        },
    });
});
