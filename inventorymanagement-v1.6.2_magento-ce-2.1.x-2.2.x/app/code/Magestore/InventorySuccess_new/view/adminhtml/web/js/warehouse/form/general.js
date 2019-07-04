/*
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/provider',
    'Magento_Ui/js/modal/confirm'
], function ($, _, Element, confirm) {
    'use strict';

    return Element.extend({
        defaults: {
            clientConfig: {
                urls: {
                    save: '${ $.submit_url }',
                    beforeSave: '${ $.validate_url }'
                }
            }
        },

        /**
         * Initializes provider component.
         *
         * @returns {Provider} Chainable.
         */
        initialize: function () {
            this._super()
                .initClient();

            return this;
        },

        /**
         * Saves currently available data.
         *
         * @param {Object} [options] - Addtitional request options.
         * @returns {Provider} Chainable.
         */
        save: function (options) {
            var data = this.get('data'),
                self = this;

            if(data.status == 0){
                confirm({
                    content: $.mage.__('Disabled location can not be used to order, shipment or refund. ' +
                        'Are you sure to mark this location as disable?'),
                    actions: {
                        confirm: function(){
                            self.client.save(data, options);
                        }
                    }
                });
            }
            return this;
        }
    });
});
