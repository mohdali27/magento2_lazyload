/*
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'Magento_Ui/js/grid/columns/thumbnail'
], function ($, ko, thumbnail) {
    'use strict';
    
    return thumbnail.extend({
        defaults: {
            bodyTmpl: 'Magestore_InventorySuccess/grid/cells/thumbnail',
            fieldClass: {
                'data-grid-thumbnail-cell': true
            }
        },
        getFieldHandler: function (row) {

        }
    });
});
