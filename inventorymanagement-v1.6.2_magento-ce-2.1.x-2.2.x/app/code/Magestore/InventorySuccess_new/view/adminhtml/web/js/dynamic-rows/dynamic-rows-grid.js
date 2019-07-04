/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/dynamic-rows/dynamic-rows-grid'
], function (dynamicRowsGrid) {
    'use strict';

    return dynamicRowsGrid.extend({
        /**
         * @inheritDoc
         */
        setInitialProperty: function () {
            // We do not need init (used for default init)
            return this;
        },

        /**
         * @inheritDoc
         */
        getNewData: function (data) {
            var changes = [],
                tmpObj = {};

            if (data.length !== this.relatedData.length) {
                // Update algorithm for check changes data
                _.each(this.relatedData, function (obj) {
                    tmpObj[obj[this.identificationDRProperty]] = true;
                }, this);

                _.each(data, function (obj) {
                    if (!tmpObj[obj[this.identificationDRProperty]]) {
                        changes.push(obj);
                    }
                }, this);
            }

            return changes;
        },

        /**
         * @inheritDoc
         */
        _checkGridData: function (data) {
            // Init grid data
            var cacheLength = this.cacheGridData.length;
            if (0 === cacheLength) {
                return data;
            }

            // Remove or regenerate item
            var curData = data.length;
            if (curData <= cacheLength) {
                return [];
            }

            // Add item(s)
            var changes = [], obj = {};

            this.cacheGridData.each(function (record) {
              obj[record[this.map[this.identificationDRProperty]]] = true;
            }, this);

            data.each(function (record, index) {
                if (!obj[record[this.map[this.identificationDRProperty]]]) {
                    changes.push(data[index]);
                }
            }, this);

            return changes;
        }
    });
});
