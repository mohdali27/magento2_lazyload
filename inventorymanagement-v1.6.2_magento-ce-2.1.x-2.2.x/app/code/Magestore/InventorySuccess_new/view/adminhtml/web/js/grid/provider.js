/*
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/grid/provider'
], function (_, gridProvider) {
    'use strict';

    return gridProvider.extend({
        /**
         * Reloads data with current parameters.
         *
         * @returns {Promise} Reload promise object.
         */
        reload: function (options) {
            // Fix limit number of params
            var params = this.params;
            if (undefined !== params.filters_modifier) {
                params = _.extendOwn({}, params);
                params.filters_modifier = JSON.stringify(params.filters_modifier);
            }

            var request = this.storage().getData(params, options);

            this.trigger('reload');

            request
                .done(this.onReload)
                .fail(this.onError.bind(this));

            return request;
        }
    });
});
