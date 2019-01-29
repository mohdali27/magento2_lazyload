/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 /*jshint jquery:true*/
 define([
    //'uiComponent',
    'Magento_Ui/js/grid/columns/multiselect',
    'Magento_Catalog/js/price-utils'
], function (Component, utils, paging) {
    'use strict';
    return Component.extend({
        initialize: function () {
            this._super();
            this.totalSellerPrice = this.totalSelected;
        },
        updateState: function () {
            this.totalSellerPrice = this.totalSelected;
        }
    });
});