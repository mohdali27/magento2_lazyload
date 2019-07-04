/*
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magestore_InventorySuccess/js/element/import-button'
], function ($, Component) {
    'use strict';

    return Component.extend({
        handleOnclick: function () {
            $('#import-send-stock-modal').modal('openModal');
        }
    });
});
