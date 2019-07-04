/*
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/form',
], function (Form) {
    'use strict';

    return Form.extend({

        /**
         * Initialize adapter handlers.
         *
         * @returns {Object}
         */
        initAdapter: function () {
            // adapter.on({
            //     'reset': this.reset.bind(this),
            //   //  'save': this.save.bind(this, true, {}),
            //   //  'saveAndContinue': this.save.bind(this, false, {})
            // }, this.selectorPrefix, this.eventPrefix);

            return this;
        }
    });
});
