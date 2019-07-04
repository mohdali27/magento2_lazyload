/*
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function (_, registry, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            skipValidation: false,
            imports: {
                update: '${ $.parentName }.country_id:value'
            }
        },

        initFilter: function () {
            var filter = this.filterBy;
            var country = registry.get(this.parentName + '.' + 'country_id');

            this.filter(country.value(), filter.field);

            this.setLinks({
                filter: filter.target
            }, 'imports');

            return this;
        },

        /**
         * @param {String} value
         */
        update: function (value) {
            var country = registry.get(this.parentName + '.' + 'country_id'),
                options = country.indexedOptions,
                isRegionRequired,
                option;

            if (!value) {
                return;
            }
            option = options[value];
            if (option) {
                registry.get(this.customName, function (input) {
                    isRegionRequired = !!option['is_region_required'];
                    input.validation['required-entry'] = isRegionRequired;
                    input.required(isRegionRequired);
                });
            }
            this.filter(value, this.filterBy.field);
        },

        /**
         * Filters 'initialOptions' property by 'field' and 'value' passed,
         * calls 'setOptions' passing the result to it
         *
         * @param {*} value
         * @param {String} field
         */
        filter: function (value, field) {

            var country = registry.get(this.parentName + '.' + 'country_id'),
                option = country.indexedOptions[value];

            this._super(value, field);

            if (option && option['is_region_visible'] === false) {
                // hide select and corresponding text input field if region must not be shown for selected country
                this.setVisible(false);

                if (this.customEntry) {
                    this.toggleInput(false);
                }
            }
        }
    });
});

