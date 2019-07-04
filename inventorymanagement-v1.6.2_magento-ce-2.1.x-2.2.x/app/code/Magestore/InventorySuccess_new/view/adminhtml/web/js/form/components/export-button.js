/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/form/components/button',
    'jquery',
    'uiRegistry',
    'mageUtils',
], function (Element, $, registry, utils) {
    'use strict';

    return Element.extend({

        /**
         * Apply action on target component,
         * but previously create this component from template if it is not existed
         *
         * @param {Object} action - action configuration
         */
        applyAction: function (action) {
            var targetName = action.targetName,
                params = utils.copy(action.params) || [],
                actionName = action.actionName,
                target;

            if (!registry.has(targetName)) {
                this.getFromTemplate(targetName);
            }
            target = registry.async(targetName);

            if (target && typeof target === 'function' && actionName) {
                params.unshift(actionName);
                target.apply(target, params);
                if (actionName == 'save') {
                    $('body').trigger('processStop');
                }
            }
        }
    });
});
