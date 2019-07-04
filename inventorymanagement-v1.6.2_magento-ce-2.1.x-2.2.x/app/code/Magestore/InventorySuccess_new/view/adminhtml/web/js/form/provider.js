/*
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/form/provider',
], function (_, Provider) {
    'use strict';

    return Provider.extend({
        /**
         * Saves currently available data.
         *
         * @param {Object} [options] - Addtitional request options.
         * @returns {Provider} Chainable.
         */
        save: function (options) {
            var data = this.get('data');

            // JSON serialize for data.links
            if (undefined !== data.links) {
                data = _.extendOwn({}, data);
                data.links = JSON.stringify(data.links);
            }

            this.client.save(data, options);

            return this;
        },
    });
});
