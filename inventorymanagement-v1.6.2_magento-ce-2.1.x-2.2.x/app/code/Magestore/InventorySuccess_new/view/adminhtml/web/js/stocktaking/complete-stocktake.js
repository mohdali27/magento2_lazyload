/*
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magestore_InventorySuccess/js/form/alert-before-submit'
], function ($, AlertSubmit) {
    'use strict';

    return AlertSubmit.extend({
        defaults: {
            param_name: 'confirm',
            message: 'Are you sure to complete stocktaking?'
        }
    });
});
