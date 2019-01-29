/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
var config = {
    map: {
        '*': {
            colorpicker: 'Webkul_Marketplace/js/colorpicker',
            verifySellerShop: 'Webkul_Marketplace/js/account/verify-seller-shop',
            editSellerProfile: 'Webkul_Marketplace/js/account/edit-seller-profile',
            sellerDashboard: 'Webkul_Marketplace/js/account/seller-dashboard',
            sellerAddProduct: 'Webkul_Marketplace/js/product/seller-add-product',
            sellerEditProduct: 'Webkul_Marketplace/js/product/seller-edit-product',
            sellerCreateConfigurable: 'Webkul_Marketplace/js/product/attribute/create',
            sellerProductList: 'Webkul_Marketplace/js/product/seller-product-list',
            sellerOrderHistory: 'Webkul_Marketplace/js/order/history',
            sellerOrderShipment: 'Webkul_Marketplace/js/order/shipment',
            colorPickerFunction: 'Webkul_Marketplace/js/color-picker-function',
            productGallery:     'Webkul_Marketplace/js/product-gallery',
            baseImage:          'Webkul_Marketplace/catalog/base-image-uploader',
            newVideoDialog:  'Webkul_Marketplace/js/new-video-dialog',
            openVideoModal:  'Webkul_Marketplace/js/video-modal',
            productAttributes:  'Webkul_Marketplace/catalog/product-attributes',
            configurableAttribute:  'Webkul_Marketplace/catalog/product/attribute',
            relatedProduct: 'Webkul_Marketplace/js/product/related-product',
            upsellProduct: 'Webkul_Marketplace/js/product/upsell-product',
            crosssellProduct: 'Webkul_Marketplace/js/product/crosssell-product',
            notification : 'Webkul_Marketplace/js/notification',
            separateSellerProductList: 'Webkul_Marketplace/js/product/separate-seller-product-list',
            formButtonAction: 'Webkul_Marketplace/js/form-button-action',
            'Magento_Ui/js/form/element/date':  'Webkul_Marketplace/js/form/element/date'
        }
    },
    paths: {
        "colorpicker": 'js/colorpicker'
    },
    "shim": {
        "colorpicker" : ["jquery"]
    }
};
