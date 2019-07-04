/*
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/select'
], function (Abstract) {
    return Abstract.extend({
        onFocus: function (data) {
            data.previewValue = data.value();
        },

        onChange: function (data) {
            try {
                var recordData = this.recordData();
                recordData._each(function (el, key) {
                    if (el.warehouse_id == data.value() && data.parentScope.split(".")[3] != key) {
                        el.warehouse_id = data.previewValue;
                    }
                });
                this.recordData(recordData);
                this.reload();
            } catch (err) {
                console.log(err);
            }
        }
    });
});
