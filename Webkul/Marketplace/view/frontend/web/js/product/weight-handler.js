define([
    'jquery'
], function ($) {
    'use strict';

    return {

        $weightSwitcher: $('[data-role=weight-switcher]'),
        $weight: $('#weight'),

        /**
         * Hide weight switcher
         */
        hideWeightSwitcher: function () {
            this.$weightSwitcher.hide();
        },

        /**
         * Is locked
         * @returns {*}
         */
        isLocked: function () {
            return this.$weight.is('[data-locked]');
        },

        /**
         * Disabled
         */
        disabled: function () {
            this.$weight.removeClass('required-entry');
            this.$weight.removeClass('mage-error');
            $('#weight-error').remove();
            this.$weight.addClass('ignore-validate').prop('disabled', true);
        },

        /**
         * Enabled
         */
        enabled: function () {
            this.$weight.addClass('required-entry');
            this.$weight.removeClass('ignore-validate').prop('disabled', false);
        },

        /**
         * Switch Weight
         * @returns {*}
         */
        switchWeight: function () {
            return this.productHasWeight() ? this.enabled() : this.disabled();
        },

        /**
         * Product has weight
         * @returns {Bool}
         */
        productHasWeight: function () {
            return $('input:checked', this.$weightSwitcher).val() === '1';
        },

        /**
         * Notify product weight is changed
         * @returns {*|jQuery}
         */
        notifyProductWeightIsChanged: function () {
            return $('input:checked', this.$weightSwitcher).trigger('change');
        },

        /**
         * Change
         * @param {String} data
         */
        change: function (data) {
            var value = data !== undefined ? +data : !this.productHasWeight();

            $('input[value=' + value + ']', this.$weightSwitcher).prop('checked', true);
        },

        /**
         * Constructor component
         */
        'Webkul_Marketplace/js/product/weight-handler': function () {
            this.bindAll();
            this.switchWeight();
        },

        /**
         * Bind all
         */
        bindAll: function () {
            this.$weightSwitcher.find('input').on('change', this.switchWeight.bind(this));
        }
    };
});
