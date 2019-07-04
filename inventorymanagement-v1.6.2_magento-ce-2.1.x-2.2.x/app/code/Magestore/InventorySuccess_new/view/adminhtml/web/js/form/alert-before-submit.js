/*
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/form/form',
    'Magento_Ui/js/modal/confirm'
], function ($, $t, Form, ModalConfirm) {
    'use strict';

    return Form.extend({
        defaults: {
            param_name: 'confirm',
            message: 'Are you sure to adjust stock?'
        },
        save: function (redirect, data) {
            if(data != undefined){
                if(data.back != undefined && data.back == this.param_name) {
                    var self = this;
                    var message = $t(this.message);
                    ModalConfirm({
                        content: message,
                        actions: {
                            confirm: function () {
                                self.validate();
                                if (!self.additionalInvalid && !self.source.get('params.invalid')) {
                                    self.setAdditionalData(data)
                                        .submit(redirect);
                                }
                            },
                            cancel: function () {
                                return false;
                            },
                            always: function () {
                                return false;
                            }
                        }
                    });
                    return false;
                }
            }
            this.validate();
            if (!this.additionalInvalid && !this.source.get('params.invalid')) {
                this.setAdditionalData(data)
                    .submit(redirect);
            }
        }
    });
});
