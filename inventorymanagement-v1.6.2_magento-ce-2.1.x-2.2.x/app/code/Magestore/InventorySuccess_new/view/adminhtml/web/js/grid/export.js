/*
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'Magento_Ui/js/grid/export'
], function ($, ko, exports) {
    'use strict';

    return exports.extend({
        applyOption: function () {
            var option = this.getActiveOption(),
                url = this.buildOptionUrl(option);
            var product_id = this.getProductParam();
            var warehouse_id = this.getWarehouseParam();
            var str = (url.indexOf('/gridToCsv/') > 0) ? '/gridToCsv/' : '/gridToXml/';
            if(product_id){
                if(url.indexOf(str) > 0){
                    location.href = this.buildUrl(url,str,'product_id/'+product_id);
                }
            } else if(warehouse_id){
                if(url.indexOf(str) > 0){
                    location.href = this.buildUrl(url,str,'warehouse_id/'+warehouse_id);
                }
            }else {
                location.href = url;
            }
        },
        getProductParam : function (){
            var str = 'product/edit/id/';
            return this.getParamsOption(str);
        },
        getWarehouseParam: function(){
            var str = 'warehouse/edit/id/';
            return this.getParamsOption(str);
        },
        getParamsOption:function(str){
            var currentUrl =  window.location.href;
            if(currentUrl.indexOf(str) > 0){
                var start = currentUrl.indexOf(str);
                currentUrl = currentUrl.slice(start+str.length);
                var end = currentUrl.indexOf('/');
                var warehouse_id = currentUrl.substr(0,end);
                return warehouse_id;
            }
        },
        buildUrl : function (url,str,param){
            var flag = url.indexOf(str);
            var exportUrl = url.substr(0,flag);
            exportUrl = exportUrl+str+param+'/'+url.substr(flag+str.length,url.length);
            return exportUrl;
        }
    });
});
