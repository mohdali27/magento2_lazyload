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
    "jquery",
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    "jquery/ui"
], function ($, $t, alert) {
    'use strict';
    $.widget('mage.editSellerProfile', {
        options: {
            backUrl: '',
            errorMessageForAllowedExtensions: $t('Invalid Image Extension. Allowed extensions are jpg, jpef, png, gif'),
            confirmMessageForBannerDelete: $t('Are you sure you want to delete this banner?'),
            confirmMessageForLogoDelete: $t('Are you sure you want to delete this logo?'),
            errorMessage: $t('Invalid address.'),
            ajaxErrorMessage: $t('There was error during fetching results.'),
            logoImageDeleteMouseOverWidth: '22px',
            logoImageDeleteMouseOutWidth: '20px',
            opacity: '.7'
        },
        _create: function () {
            var self = this;
            $(self.options.countryPicSelector).on('change', function () {
                self.callGoogleMapApiAjaxFunction(this);
            });

            $(self.options.bannerPicSelector).on('change', function () {
                var imagename=$(this).val().toLowerCase();
                var image=imagename.split(".");
                var extIndex = image.length - 1;
                image=image[extIndex];
                if (image!='jpg' && image!='jpeg' && image!='png' && image!='gif') {
                    alert({
                        content: self.options.errorMessageForAllowedExtensions
                    });
                    $(this).val('');
                }
            });

            $(self.options.logoPicSelector).on('change', function () {
                var imagename=$(this).val().toLowerCase();
                var image=imagename.split(".");
                var extIndex = image.length - 1;
                image=image[extIndex];
                if (image!='jpg' && image!='jpeg' && image!='png' && image!='gif') {
                    alert({
                        content: self.options.errorMessageForAllowedExtensions
                    });
                    $(this).val('');
                }
            });

            $(self.options.leftButtonSelector).insertAfter(self.options.buttonsSetLastSelector);
            
            $(self.options.inputTextSelector).on('change', function () {
                var thisValue = $(this).val();
                var regex = /(<([^>]+)>)/ig;
                var result = thisValue.replace(regex,"");
                $(this).val(result);
            });
            
            $(self.options.profileimageSetSpanSelector).click(function (event) {
                var dicisionapp = confirm(self.options.confirmMessageForBannerDelete);
                if (dicisionapp) {
                    var thisthis = $(this);
                    $(self.options.bannerSelector).css('opacity', self.options.opacity);
                    $.ajax({
                        url: self.options.bannerDeleteAjaxUrl,
                        type: "POST",
                        data: {
                            file:'banner'
                        },
                        dataType: 'html',
                        success:function (response) {
                            thisthis.parent(self.options.setimageSelector).remove();
                        },
                        error: function (response) {
                            alert({
                                content: self.options.ajaxErrorMessage
                            });
                        }
                    });
                }
            });

            $(self.options.profileImageDeleteSelector).mouseover(function (event) {
                $(event.target).css('width', self.options.logoImageDeleteMouseOverWidth);
            });

            $(self.options.profileImageDeleteSelector).mouseout(function (event) {
                $(event.target).css('width', self.options.logoImageDeleteMouseOutWidth);
            });

            $(self.options.logoImageSetSpanSelector).click(function (event) {
                var dicisionapp = confirm(self.options.confirmMessageForLogoDelete);
                if (dicisionapp) {
                    var thisthis = $(this);
                    $(self.options.logoSelector).css('opacity', self.options.opacity);
                    $.ajax({
                        url: self.options.logoDeleteAjaxUrl,
                        type: "POST",
                        data: {
                            file:'logo'
                        },
                        dataType: 'html',
                        success:function (response) {
                            thisthis.parent(self.options.setimageSelector).remove();
                        },
                        error: function (response) {
                            alert({
                                content: self.options.ajaxErrorMessage
                            });
                        }
                    });
                }
            });

            $(self.options.logoImageDeleteSelector).mouseover(function (event) {
                $(event.target).css('width', self.options.logoImageDeleteMouseOverWidth);
            });

            $(self.options.logoImageDeleteSelector).mouseout(function (event) {
                $(event.target).css('width', self.options.logoImageDeleteMouseOutWidth);
            });
        },

        callGoogleMapApiAjaxFunction: function (e) {
            var self = this;
            $(self.options.countryImgPrevSelector).attr(
                'src',
                self.options.countryImgPrev+'/'+$(e).val()+'.png'
            );
            //address which you want Longitude and Latitude
            var address=$(e).find('option[value="'+$(e).val()+'"]').text();
            $.ajax({
                type: "GET",
                dataType: "json",
                url: "//maps.googleapis.com/maps/api/geocode/json",
                data: {
                    'address': address,
                    'sensor':false
                },
                success: function (response) {
                    if (response.results.length) {
                        $(self.options.countryLatitudeSelector).val(
                            response.results[0].geometry.location.lat
                        );
                        $(self.options.countryLongitudeSelector).val(
                            response.results[0].geometry.location.lng
                        );
                    } else {
                        $(self.options.countryLatitudeSelector).val(
                            self.options.errorMessage
                        );
                        $(self.options.countryLongitudeSelector).val(
                            self.options.errorMessage
                        );
                   }
                },
                error: function (response) {
                    alert({
                        content: self.options.ajaxErrorMessage
                    });
                }
            });
        }
    });
    return $.mage.editSellerProfile;
});
