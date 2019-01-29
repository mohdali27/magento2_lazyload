define([
    'jquery',
    'underscore',
    'mageUtils',
    'rjsResolver',
    'uiLayout',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'uiElement'
], function ($, _, utils, resolver, layout, alert, $t, Element) {
    'use strict';

    return Element.extend({
        defaults: {
            firstLoad: true,
            storageConfig: {
                component: 'Magento_Ui/js/grid/data-storage',
                provider: '${ $.storageConfig.name }',
                name: '${ $.name }_storage',
                updateUrl: '${ $.update_url }'
            },
            listens: {
                params: 'onParamsChange',
                requestConfig: 'updateRequestConfig'
            }
        },

        /**
         * Initializes provider component.
         *
         * @returns {Provider} Chainable.
         */
        initialize: function () {
            utils.limit(this, 'onParamsChange', 5);
            _.bindAll(this, 'onReload');

            this._super()
                .initStorage()
                .clearData();

            // Load data when there will
            // be no more pending assets.
            resolver(this.reload, this);

            return this;
        },

        /**
         * Initializes storage component.
         *
         * @returns {Provider} Chainable.
         */
        initStorage: function () {
            layout([this.storageConfig]);

            return this;
        },

        /**
         * Clears provider's data properties.
         *
         * @returns {Provider} Chainable.
         */
        clearData: function () {
            this.setData({
                items: [],
                totalRecords: 0
            });

            return this;
        },

        /**
         * Overrides current data with a provided one.
         *
         * @param {Object} data - New data object.
         * @returns {Provider} Chainable.
         */
        setData: function (data) {
            data = this.processData(data);

            this.set('data', data);

            return this;
        },

        /**
         * Processes data before applying it.
         *
         * @param {Object} data - Data to be processed.
         * @returns {Object}
         */
        processData: function (data) {
            var items = data.items;

            _.each(items, function (record, index) {
                record._rowIndex = index;
            });

            return data;
        },

        /**
         * Reloads data with current parameters.
         *
         * @returns {Promise} Reload promise object.
         */
        reload: function (options) {
            if (this.params.namespace=='marketplace_related_product_listing') {
                this.params.current_product_id = $("input[name='product_id']").val();
            } else if (this.params.namespace=='marketplace_crosssell_product_listing') {
                this.params.current_product_id = $("input[name='product_id']").val();
            } else if (this.params.namespace=='marketplace_upsell_product_listing') {
                this.params.current_product_id = $("input[name='product_id']").val();
            } else if (this.params.namespace=='marketplace_orders_listing') {
                var arr = window.location.pathname.split('/customer_id/');
                if (arr[1] !== undefined) {
                    if(parseInt(arr[1])) {
                        this.params.customer_id = parseInt(arr[1]);
                    }
                }
            }
            
            var request = this.storage().getData(this.params, options);

            this.trigger('reload');

            request
                .done(this.onReload)
                .fail(this.onError);

            return request;
        },

        /**
         * Handles changes of 'params' object.
         */
        onParamsChange: function () {
            // It's necessary to make a reload only
            // after the initial loading has been made.
            if (!this.firstLoad) {
                this.reload();
            }
        },

        /**
         * Handles reload error.
         */
        onError: function (xhr) {
            if (xhr.statusText === 'abort') {
                return;
            }

            alert({
                content: $t('Something went wrong.')
            });
        },

        /**
         * Handles successful data reload.
         *
         * @param {Object} data - Retrieved data object.
         */
        onReload: function (data) {
            this.firstLoad = false;

            this.setData(data)
                .trigger('reloaded');
        },

        /**
         * Updates storage's request configuration
         *
         * @param {Object} requestConfig
         */
        updateRequestConfig: function (requestConfig) {
            if (this.storage()) {
                _.extend(this.storage().requestConfig, requestConfig);
            }
        }
    });
});
