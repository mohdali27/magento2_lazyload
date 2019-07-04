
/*
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global $, $H */

define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'uiRegistry',
    'mage/adminhtml/grid',
], function (jQuery, alert, confirm, registry) {
    return function (config) {
        var selectedProducts = config.selectedProducts,
            warehouseProducts = $H(selectedProducts),
            gridJsObject = window[config.gridJsObjectName],
            gridJsInforObject = window[config.gridJsInforName],
            hiddenInputField = config.hiddenInputField,
            checkboxOnly = config.checkboxOnly,
            saveBtn = config.saveBtn,
            saveUrl = config.saveUrl,
            changeWarehouseBtn = config.changeWarehouseBtn,
            deleteProductBtn = config.deleteProductBtn,
            deleteProductUrl = config.deleteProductUrl,
            popupUrl = config.popupUrl,
            gridJsObjectParent = window[config.gridJsObjectParent],
            tabIndex = 1000;

        $(hiddenInputField).value = Object.toJSON(warehouseProducts);

        /**
         * Register Warehouse Product
         *
         * @param {Object} grid
         * @param {Object} element
         * @param {Boolean} checked
         */
        function registerWarehouseProduct(grid, element, checked) {
            if(!checkboxOnly){
                if (checked) {
                    var value = {};
                    if (element.totalQtyElement) {
                        element.totalQtyElement.show();
                        element.totalQtyElement.up('div').down('span').hide();
                        element.totalQtyElement.disabled = false;
                        value.total_qty = element.totalQtyElement.value;
                        warehouseProducts.set(element.value, JSON.stringify(value));
                    }
                    if(element.oldQtyElement){
                        value.old_qty = element.oldQtyElement.value;
                        warehouseProducts.set(element.value, JSON.stringify(value));
                    }
                    if (element.shelfLocationElement) {
                        element.shelfLocationElement.show();
                        element.shelfLocationElement.up('div').down('span').hide();
                        element.shelfLocationElement.disabled = false;
                        value.shelf_location = element.shelfLocationElement.value;
                        warehouseProducts.set(element.value, JSON.stringify(value));
                    }
                } else {
                    if (element.totalQtyElement) {
                        element.totalQtyElement.hide();
                        element.totalQtyElement.up('div').down('span').show();
                        element.totalQtyElement.disabled = true;
                    }
                    if (element.shelfLocationElement) {
                        element.shelfLocationElement.hide();
                        element.shelfLocationElement.up('div').down('span').show();
                        element.shelfLocationElement.disabled = true;
                    }
                    warehouseProducts.unset(element.value);
                }
            }else{
                if (checked) {
                    warehouseProducts.set(element.value, 1);
                }else{
                    warehouseProducts.unset(element.value);
                }
            }
            $(hiddenInputField).value = Object.toJSON(warehouseProducts);
        }

        /**
         * Click on product row
         *
         * @param {Object} grid
         * @param {String} event
         */
        function warehouseProductRowClick(grid, event) {
            var trElement = Event.findElement(event, 'tr'),
                isInput = Event.element(event).tagName === 'INPUT',
                checked = false,
                checkbox = null;

            if (trElement) {
                checkbox = Element.getElementsBySelector(trElement, 'input');

                if (checkbox[0]) {
                    checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                    gridJsObject.setCheckboxChecked(checkbox[0], checked);
                }
            }
        }

        /**
         * Change product total qty
         *
         * @param {String} event
         */
        function totalQtyChange(event) {
            var element = Event.element(event);
            if (element && element.checkboxElement && element.checkboxElement.checked) {
                if(element.value == '' || isNaN(element.value) || element.value<0){
                    element.value = 0;
                    element.select();
                }
                var value = JSON.parse(warehouseProducts.get(element.checkboxElement.value));
                value = value ? value : {};
                value.total_qty = element.value;
                warehouseProducts.set(element.checkboxElement.value, JSON.stringify(value));
                $(hiddenInputField).value = Object.toJSON(warehouseProducts);
            }
        }

        /**
         * Change product shelf location
         *
         * @param {String} event
         */
        function shelfLocationChange(event) {
            var element = Event.element(event);
            if (element && element.checkboxElement && element.checkboxElement.checked) {
                var value = JSON.parse(warehouseProducts.get(element.checkboxElement.value));
                value = value ? value : {};
                value.shelf_location = element.value;
                warehouseProducts.set(element.checkboxElement.value, JSON.stringify(value));
                $(hiddenInputField).value = Object.toJSON(warehouseProducts);
            }
        }

        /**
         * show product infor popup
         * 
         * @param event
         */
        function showPopupInfor(event){
            var productId = event.element().getAttribute('value');
            var productName = event.element().getAttribute('product-name');
            openModal('product_infor_grid_container');
            jQuery('#product_infor_grid_name').html(productName);
            var filters = $$('#' + gridJsInforObject.containerId + ' [data-role="filters-form"] input', '#' +
                gridJsInforObject.containerId + ' [data-role="filters-form"] select');
            for (var i in filters) {
                filters[i].value = '';
            }
            return warehouseAction(popupUrl, {id: productId}, gridJsInforObject);
        }

        /**
         * Initialize warehouse product row
         *
         * @param {Object} grid
         * @param {String} row
         */
        function warehouseProductRowInit(grid, row) {
            var checkbox = $(row).getElementsByClassName('checkbox')[0],
                totalQty = $(row).down('input[name=sum_total_qty]'),
                oldQty = $(row).down('input[name=sum_total_qty_old]'),
                shelfLocation = $(row).down('input[name=shelf_location]'),
                viewButton = $(row).down('a[class=view_infor]');
            
            if(checkbox){
                if (totalQty || shelfLocation || oldQty) {
                    if (totalQty) {
                        checkbox.totalQtyElement = totalQty;
                        totalQty.checkboxElement = checkbox;
                        totalQty.disabled = !checkbox.checked;
                        totalQty.tabIndex = tabIndex++;
                        Event.observe(totalQty, 'keyup', totalQtyChange);
                    }
                    if(oldQty){
                        checkbox.oldQtyElement = oldQty;
                    }
                    if (shelfLocation) {
                        checkbox.shelfLocationElement = shelfLocation;
                        shelfLocation.checkboxElement = checkbox;
                        shelfLocation.disabled = !checkbox.checked;
                        shelfLocation.tabIndex = tabIndex++;
                        Event.observe(shelfLocation, 'keyup', shelfLocationChange);
                    }
                }
                var values = warehouseProducts.get(checkbox.value);
                if(values){
                    var values = JSON.parse(values);
                    if(values.total_qty)
                        checkbox.totalQtyElement.value = values.total_qty;
                    if(values.shelf_location)
                        checkbox.shelfLocationElement.value = values.shelf_location;
                    gridJsObject.setCheckboxChecked(checkbox, true);
                };
            }
            if(viewButton){
                Event.observe(viewButton, 'click', showPopupInfor);
            }
        }

        /**
         * send ajax request for when save warehouse information
         *
         * @returns {*}
         */
        function saveWarehouseProduct() {
            if($(hiddenInputField).value=='{}'){
                return alert({
                    title: jQuery.mage.__('Error'),
                    content: jQuery.mage.__('Please select products to update.')
                });
            }
            return warehouseAction(saveUrl, null, gridJsObject, function(){
                warehouseProducts = $H([]);
                $(hiddenInputField).value = Object.toJSON(warehouseProducts);
            });
        } 

        /**
         * change warehouse to reload product list
         * 
         * @param event
         */
        function changeWarehouse(event) {
            warehouseProducts = $H([]);
            $(hiddenInputField).value = Object.toJSON(warehouseProducts);
            var warehouseId = event.target.value;
            var params = {'warehouse_id': warehouseId};
            if(warehouseId && warehouseId!=0){
                $$('#save_warehouse_product')[0].show();
            }else{
                $$('#save_warehouse_product')[0].hide();
            }
            warehouseAction(gridJsObject.url, params, gridJsObject);
        }

        /**
         * remove product from warehouse
         * 
         * @param event
         */
        function deleteProduct(event){
            if($(hiddenInputField).value=='{}'){
                return alert({
                    title: jQuery.mage.__('Error'),
                    content: jQuery.mage.__('Please select products to delete.')
                });
            }
            confirm({
                content: jQuery.mage.__('Are you sure to delete these products'),
                actions: {
                    confirm: function () {
                        if($(hiddenInputField).value!='{}'){
                            warehouseAction(deleteProductUrl, null, gridJsObject, function(){
                                // console.log('a');
                                // registry.getItems('os_warehouse_product_none_in_warehouse_listing');
                                // window.location.reload();
                                if(gridJsObjectParent)
                                    gridJsObjectParent.doFilter();
                            });
                            warehouseProducts = $H([]);
                            $(hiddenInputField).value = Object.toJSON(warehouseProducts);
                            return;
                        }
                    }
                }
            });
        }

        /**
         * send ajax request for warehouse action
         * 
         * @param url
         * @returns {*}
         */
        function warehouseAction(url, params, gridObject, callback){
            var filters = $$('#' + gridObject.containerId + ' [data-role="filters-form"] input', '#' +
                gridObject.containerId + ' [data-role="filters-form"] select');
            var elements = [];
            for (var i in filters) {
                if (filters[i].value && filters[i].value.length) elements.push(filters[i]);
            }
            var url = gridObject._addVarToUrl(url, gridObject.filterVar, Base64.encode(Form.serializeElements(elements)));

            gridObject.reloadParams = gridObject.reloadParams || {};
            gridObject.reloadParams.form_key = FORM_KEY;
            gridObject.reloadParams.selected_product = $(hiddenInputField).value;
            if(params){
                jQuery.each(params, function(index, value){
                    gridObject.reloadParams[index] = value;
                })
            }
            var ajaxSettings = {
                url: url + (url.match(new RegExp('\\?')) ? '&ajax=true' : '?ajax=true' ),
                showLoader: true,
                method: 'post',
                context: jQuery('#' + gridObject.containerId),
                data: gridObject.reloadParams,
                error: gridObject._processFailure.bind(gridObject),
                complete: gridObject.initGridAjax.bind(gridObject),
                dataType: 'html',
                success: function(data, textStatus, transport) {
                    gridObject._onAjaxSeccess(data, textStatus, transport);
                    if(callback)
                        callback();
                }.bind(gridObject)
            };
            jQuery('#' + gridObject.containerId).trigger('gridajaxsettings', ajaxSettings);
            var ajaxRequest = jQuery.ajax(ajaxSettings);
            jQuery('#' + gridObject.containerId).trigger('gridajax', ajaxRequest);
            return ajaxRequest;
        }

        function openModal(id){
            $(id).addClassName('_show');
            jQuery('.modals-overlay').css('position', 'fixed');
        }

        function closeModal(id){
            if(typeof id != "string")
                id = 'product_infor_grid_container';
            $(id).removeClassName('_show');
            jQuery('.modals-overlay').css('position', 'relative');
        }
        
        if($('product_infor_grid_close'))
            Event.observe('product_infor_grid_close', 'click', closeModal);

        gridJsObject.rowClickCallback = warehouseProductRowClick;
        gridJsObject.initRowCallback = warehouseProductRowInit;
        gridJsObject.checkboxCheckCallback = registerWarehouseProduct;

        if(saveBtn){
            if(changeWarehouseBtn) {
                $$('#' + saveBtn)[0].hide();
            }
            var url = window.location.href;
            if((url.indexOf('/warehouse_id/') > 0)){
                $$('#' + saveBtn)[0].show();
            }
            Event.observe(saveBtn, 'click', saveWarehouseProduct);
        }
        if(changeWarehouseBtn)
            Event.observe(changeWarehouseBtn, 'change', changeWarehouse);
        if(deleteProductBtn)
            Event.observe(deleteProductBtn, 'click', deleteProduct);

        if (gridJsObject.rows) {
            gridJsObject.rows.each(function (row) {
                warehouseProductRowInit(gridJsObject, row);
            });
        }

        
            
    };
});
