/*
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'mageUtils',
    'underscore',
    'uiLayout',
    'Magento_Ui/js/dynamic-rows/dynamic-rows',
    'uiRegistry',
    'mage/translate'
], function (ko, utils, _, layout, dynamicRow, registry, $t) {
    'use strict';

    return dynamicRow.extend({
        clearAll: function () {
            this.default = this.recordData();
            this.recordData([]);
            this.columnsHeader(true);
            this.clear();
        },

        /**
         * Extends instance with default config, calls initialize of parent
         * class, calls initChildren method, set observe variable.
         * Use parent "track" method - wrapper observe array
         *
         * @returns {Object} Chainable.
         */
        initialize: function () {
            this._super()
                .initChildren()
                .initDnd()
                .setColumnsHeaderListener()
                .initDefaultRecord()
                .checkSpinner();
            this.canClearAll = ko.pureComputed(function() {
                return this.elems().length;
            }, this);
            this.canAddLocation = ko.pureComputed(function() {
                return this.elems().length != this.source.data.location.length;
            }, this);
            // console.log(this.source.data.location);
            return this;
        },

        addAll: function () {
            this.default = this.source.data.location;
            this.recordData(utils.copy(this.default));
            this.reload();
        }
    });
});
