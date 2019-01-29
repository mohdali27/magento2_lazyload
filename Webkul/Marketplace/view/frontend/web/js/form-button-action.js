/*jshint browser:true*/
define([
    "jquery"
], function ($) {
    "use strict";
    $.widget('mage.formButtonAction', {
        options: {
            fixedClass: 'wk-mp-fixed'
        },
        /**
         * Widget initialization
         * @private
         */
        _create: function() {
            this.setRequiredVariables();
            this.applyBinding();
        },
        setRequiredVariables: function() {
            this.elementBeforeObj = this.element.before(
                $('<div/>', {'class': 'wk-mp-page-actions-before'})
            ).prev();
            this.offsetTop = this.elementBeforeObj.offset().top;
            this.height = this.element.parents('.wk-mp-page-title').outerHeight();
            this.formoffset = this.element.parents('form').outerHeight()+this.offsetTop-100;
        },
        applyBinding: function() {
            this._on(window, {
                scroll: this._handlePageScroll,
                resize: this._handlePageScroll
            });
        },
        _handlePageScroll: function() {
            var isActive = ($(window).scrollTop() > this.offsetTop);
            if (this.element.attr('id') == 'wk-mp-editprofile-form') {
                var isActiveForm = (this.formoffset > $(window).scrollTop());
                if (isActive && isActiveForm) {
                    this.element.addClass(this.options.fixedClass);
                } else {
                    this.element.removeClass(this.options.fixedClass);
                }
            } else {
                if (isActive) {
                    this.element.addClass(this.options.fixedClass);
                } else {
                    this.element.removeClass(this.options.fixedClass);
                }
            }
            this.elementBeforeObj.height(isActive ? this.height: '');
        }
    });
    
    return $.mage.formButtonAction;
});