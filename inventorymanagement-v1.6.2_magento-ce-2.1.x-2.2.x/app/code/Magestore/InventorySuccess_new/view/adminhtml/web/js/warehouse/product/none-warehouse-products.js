/*
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/form/components/insert-listing',
    'mageUtils',
    'underscore',
    'Magento_Ui/js/modal/alert'
], function ($, Insert, utils, _, alert) {
    'use strict';

    return Insert.extend({
        defaults: {
            externalListingName: '${ $.ns }.${ $.ns }',
            behaviourType: 'simple',
            externalFilterMode: false,
            requestConfig: {
                method: 'POST'
            },
            externalCondition: 'nin',
            settings: {
                edit: {
                    imports: {
                        'onChangeRecord': '${ $.editorProvider }:changed'
                    }
                },
                filter: {
                    exports: {
                        'requestConfig': '${ $.externalProvider }:requestConfig'
                    }
                }
            },
            imports: {
                onSelectedChange: '${ $.selectionsProvider }:selected',
                'update_url': '${ $.externalProvider }:update_url',
                'indexField': '${ $.selectionsProvider }:indexField'
            },
            exports: {
                externalFiltersModifier: '${ $.externalProvider }:params.filters_modifier'
            },
            listens: {
                externalValue: 'updateExternalFiltersModifier updateSelections',
                indexField: 'initialUpdateListing'
            },
            modules: {
                selections: '${ $.selectionsProvider }',
                externalListing: '${ $.externalListingName }'
            }
        },

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            this._super();
            _.bindAll(this, 'updateValue', 'updateExternalValueByEditableData');

            return this;
        },

        /**
         * Updates external value, then updates value from external value
         *
         */
        save: function () {
            var self = this,
                result = $.Deferred(),
                provider = this.selections(),
                selections,
                totalSelected,
                itemsType,
                rows;

            if (!provider) {
                return result;
            }

            selections = provider && provider.getSelections();
            totalSelected = provider.totalSelected();
            itemsType = selections && selections.excludeMode ? 'excluded' : 'selected';
            rows = provider && provider.rows();
            var params = {isAjax: 'true', id: this.warehouseId};
            if (itemsType == 'excluded') {
                params.excluded = selections[itemsType];
                if (selections[itemsType].length == 0)
                    params.excluded = 'false';
            }
             params.selected = selections['selected']
            if (params.selected) {
                if (!params.selected || params.selected.length == 0) {
                    return alert({
                        title: $.mage.__('Error'),
                        content: $.mage.__('Please select products to add.')
                    });
                }
            }
            this.loading(true);
            $.ajax({
                method: "POST",
                url: this.save_url,
                data: params
            }).done(function (transport) {
                window.location.reload();
                // warehouse_list_productsJsObject.reload();
            });
        }
    });
});
