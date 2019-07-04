/*
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'mageUtils',
    'uiLayout',
    'uiElement',
    'Magento_Ui/js/lib/validation/validator',
    'uiRegistry',
    'jquery'
], function (_, utils, layout, Element, validator, registry, jQuery) {
    'use strict';

    return Element.extend({
        defaults: {
            visible: true,
            preview: '',
            required: false,
            valueChangedByUser: false,
            tooltipTpl: 'ui/form/element/helper/tooltip',
            fallbackResetTpl: 'ui/form/element/helper/fallback-reset',
            'input_type': 'input',
            description: '',
            labelVisible: true,
            label: '',
            warn: '',
            notice: '',
            customScope: '',
            default: '',
            isDifferedFromDefault: false,
            showFallbackReset: false,
            additionalClasses: {},
            isUseDefault: '',
            loading: false,

            switcherConfig: {
                component: 'Magento_Ui/js/form/switcher',
                name: '${ $.name }_switcher',
                target: '${ $.name }',
                property: 'value'
            },
            listens: {
                visible: 'setPreview',
                value: 'setDifferedFromDefault',
                '${ $.provider }:data.reset': 'reset',
                '${ $.provider }:data.overload': 'overload',
                '${ $.provider }:${ $.customScope ? $.customScope + "." : ""}data.validate': 'validate',
                'isUseDefault': 'toggleUseDefault'
            },
            error: '',
            uid: utils.uniqueid(),
            disabled: false,
            links: {
                value: '${ $.provider }:${ $.dataScope }'
            },
            placeholder: '',
            valueUpdate: false,
            focused: false,
            template: 'Magestore_InventorySuccess/form/element/barcode',
            barcodeJson: null,
            sourceElment: '',
            destinationElement: '',
            selectionsProvider: '',
            qtyElement: '',
            inputElementName: ''
        },

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            _.bindAll(this, 'reset');

            this._super()
                .setInitialValue()
                ._setClasses()
                .initSwitcher();

            return this;
        },

        /**
         * Initializes observable properties of instance
         *
         * @returns {Abstract} Chainable.
         */
        initObservable: function () {
            var rules = this.validation = this.validation || {};

            this._super()
                .observe([
                    'visible',
                    'content',
                    'value',
                    'loading'
                ]);

            this.observe('error disabled focused preview visible value warn isDifferedFromDefault')
                .observe('isUseDefault')
                .observe({
                    'required': !!rules['required-entry']
                });

            return this;
        },

        /**
         * Initializes regular properties of instance.
         *
         * @returns {Abstract} Chainable.
         */
        initConfig: function () {
            var uid = utils.uniqueid(),
                name,
                valueUpdate,
                scope;

            this._super();

            scope = this.dataScope;
            name = scope.split('.').slice(1);

            valueUpdate = this.showFallbackReset ? 'afterkeydown' : this.valueUpdate;

            _.extend(this, {
                uid: uid,
                noticeId: 'notice-' + uid,
                inputName: utils.serializeName(name.join('.')),
                valueUpdate: valueUpdate
            });

            return this;
        },

        /**
         * Initializes switcher element instance.
         *
         * @returns {Abstract} Chainable.
         */
        initSwitcher: function () {
            if (this.switcherConfig.enabled) {
                layout([this.switcherConfig]);
            }

            return this;
        },

        /**
         * Sets initial value of the element and subscribes to it's changes.
         *
         * @returns {Abstract} Chainable.
         */
        setInitialValue: function () {
            this.initialValue = this.getInitialValue();

            if (this.barcodeJson) {
                this.barcodeJson = JSON.parse(this.barcodeJson);
            } else {
                this.getBarcodeJson();
            }

            if (this.value.peek() !== this.initialValue) {
                this.value(this.initialValue);
            }

            this.on('value', this.onUpdate.bind(this));
            this.isUseDefault(this.disabled());

            return this;
        },

        /**
         * Get barcode json
         * @returns JSON
         */
        getBarcodeJson: function () {
            this.loading(true);
            jQuery('body').trigger('processStart');
            jQuery.ajax({
                method: "POST",
                url: this.getBarcodeUrl,
                data: {form_key: window.FORM_KEY},
                dataType: "json"
            }).done(function (transport) {
                this.barcodeJson = transport;
                this.loading(false);
                jQuery('body').trigger('processStop');
            }.bind(this));
        },

        /**
         * Extends 'additionalClasses' object.
         *
         * @returns {Abstract} Chainable.
         */
        _setClasses: function () {
            var additional = this.additionalClasses,
                classes;

            if (_.isString(additional) && additional.trim().length) {
                additional = this.additionalClasses.trim().split(' ');
                classes = this.additionalClasses = {};

                additional.forEach(function (name) {
                    classes[name] = true;
                }, this);
            }

            _.extend(this.additionalClasses, {
                _required: this.required,
                _error: this.error,
                _warn: this.warn,
                _disabled: this.disabled
            });

            return this;
        },

        /**
         * Gets initial value of element
         *
         * @returns {*} Elements' value.
         */
        getInitialValue: function () {
            var values = [this.value(), this.default],
                value;

            values.some(function (v) {
                if (v !== null && v !== undefined) {
                    value = v;
                    return true;
                }
                return false;
            });

            return this.normalizeData(value);
        },

        /**
         * Sets 'value' as 'hidden' propertie's value, triggers 'toggle' event,
         * sets instance's hidden identifier in params storage based on
         * 'value'.
         *
         * @returns {Abstract} Chainable.
         */
        setVisible: function (isVisible) {
            this.visible(isVisible);

            return this;
        },

        /**
         * Show element.
         *
         * @returns {Abstract} Chainable.
         */
        show: function () {
            this.visible(true);

            return this;
        },

        /**
         * Hide element.
         *
         * @returns {Abstract} Chainable.
         */
        hide: function () {
            this.visible(false);

            return this;
        },

        /**
         * Disable element.
         *
         * @returns {Abstract} Chainable.
         */
        disable: function () {
            this.disabled(true);

            return this;
        },

        /**
         * Enable element.
         *
         * @returns {Abstract} Chainable.
         */
        enable: function () {
            this.disabled(false);

            return this;
        },

        /**
         *
         * @param {(String|Object)} rule
         * @param {(Object|Boolean)} [options]
         * @returns {Abstract} Chainable.
         */
        setValidation: function (rule, options) {
            var rules = utils.copy(this.validation),
                changed;

            if (_.isObject(rule)) {
                _.extend(this.validation, rule);
            } else {
                this.validation[rule] = options;
            }

            changed = utils.compare(rules, this.validation).equal;

            if (changed) {
                this.required(!!rules['required-entry']);
                this.validate();
            }

            return this;
        },

        /**
         * Returns unwrapped preview observable.
         *
         * @returns {String} Value of the preview observable.
         */
        getPreview: function () {
            return this.value();
        },

        /**
         * Checks if element has addons
         *
         * @returns {Boolean}
         */
        hasAddons: function () {
            return this.addbefore || this.addafter;
        },

        /**
         * Checks if element has service setting
         *
         * @returns {Boolean}
         */
        hasService: function () {
            return this.service && this.service.template;
        },

        /**
         * Defines if value has changed.
         *
         * @returns {Boolean}
         */
        hasChanged: function () {
            var notEqual = this.value() !== this.initialValue;

            return !this.visible() ? false : notEqual;
        },

        /**
         * Checks if 'value' is not empty.
         *
         * @returns {Boolean}
         */
        hasData: function () {
            return !utils.isEmpty(this.value());
        },

        /**
         * Sets value observable to initialValue property.
         *
         * @returns {Abstract} Chainable.
         */
        reset: function () {
            this.value(this.initialValue);
            this.error(false);

            return this;
        },

        /**
         * Sets current state as initial.
         */
        overload: function () {
            this.setInitialValue();
            this.bubble('update', this.hasChanged());
        },

        /**
         * Clears 'value' property.
         *
         * @returns {Abstract} Chainable.
         */
        clear: function () {
            this.value('');

            return this;
        },

        /**
         * Converts values like 'null' or 'undefined' to an empty string.
         *
         * @param {*} value - Value to be processed.
         * @returns {*}
         */
        normalizeData: function (value) {
            return utils.isEmpty(value) ? '' : value;
        },

        /**
         * Validates itself by it's validation rules using validator object.
         * If validation of a rule did not pass, writes it's message to
         * 'error' observable property.
         *
         * @returns {Object} Validate information.
         */
        validate: function () {
            var value = this.value(),
                result = validator(this.validation, value, this.validationParams),
                message = !this.disabled() && this.visible() ? result.message : '',
                isValid = this.disabled() || !this.visible() || result.passed;

            this.error(message);
            this.bubble('error', message);

            //TODO: Implement proper result propagation for form
            if (!isValid) {
                this.source.set('params.invalid', true);
            }

            return {
                valid: isValid,
                target: this
            };
        },

        /**
         * Callback that fires when 'value' property is updated.
         */
        onUpdate: function () {
            this.bubble('update', this.hasChanged());

            this.validate();
            if (this.value() != '') {
                if (this.barcodeJson && typeof this.barcodeJson[this.value()] != 'undefined') {
                    // var select = registry.get(this.selectionsProvider);
                    var des = registry.get(this.destinationElement),
                        map = des.map,
                        indexField = des.identificationProperty,
                        gridData = des.relatedData,
                        indexValue = this.barcodeJson[this.value()][map[indexField]],
                        index = gridData.map(function (el) {
                            return el[indexField];
                        }).indexOf(indexValue);
                    if (index == -1) {
                        // var source = registry.get(this.sourceElement);
                        // if(!source.isRendered){
                        this.addNewRecordWithoutRender(des, map);
                        // }
                        return this.clear();
                    } else {
                        this.updateInputValue(des, map, indexField, gridData, index).done(function () {
                            this.value('')
                        }.bind(this));
                    }
                } else {
                    this.focused(false);
                    this.focused(true);
                }
            }
        },

        /**
         * Add new record when can not find it in dynamic grid
         *
         * @param {Object} des - Dynamic grid with add product
         * @param [Array] map - Mapping field for dynamic grid
         */
        addNewRecordWithoutRender: function (des, map) {
            var self = this,
                addValue = this.barcodeJson[this.value()],
                mapKey = Object.keys(map);
            var obj = {};
            obj[map[des.identificationProperty]] = addValue[map[des.identificationProperty]];
            des.insertData().push(obj);
            des.cacheGridData.push(obj);
            var obj = {};
            mapKey.each(function (el) {
                obj[el] = addValue[map[el]];
            });
            obj[self.inputElementName] = 1;
            des.recordData().push(obj);
            des.recordData.valueHasMutated();
            des.reload();
        },

        /**
         * Update input value after scan barcode
         *
         * @param {Object} des - Dynamic grid with add product
         * @param [Array] map - Mapping field for dynamic grid
         * @param String indexField - Index field of dynamic grid
         * @param [Array] gridData - Child items of dynamic grid
         * @param Int index - Index of product to add in dynamic grid
         * @returns {*}
         */
        updateInputValue: function (des, map, indexField, gridData, index) {
            var result = jQuery.Deferred(),
                qtyElement = registry.get(this.qtyElement.replace('%s', index));
            if (qtyElement) {
                var value = parseFloat(qtyElement.value());
                value = isNaN(value) ? 0 : value;
                qtyElement.value(value + 1);
                result.resolve();
            } else {
                var element = des.relatedData[index];
                if (element) {
                    var value = parseFloat(des.relatedData[index][this.inputElementName]);
                    value = isNaN(value) ? 0 : value;
                    des.relatedData[index][this.inputElementName] = value + 1;
                    des.recordData.valueHasMutated();
                }
                result.resolve();
            }
            return result;
        },

        /**
         * Restore value to default
         */
        restoreToDefault: function () {
            this.value(this.default);
        },

        /**
         * Update whether value differs from default value
         */
        setDifferedFromDefault: function () {
            var value = typeof this.value() != 'undefined' && this.value() !== null ? this.value() : '',
                defaultValue = typeof this.default != 'undefined' && this.default !== null ? this.default : '';
            this.isDifferedFromDefault(value !== defaultValue);
        },

        /**
         * @param {Boolean} state
         */
        toggleUseDefault: function (state) {
            this.disabled(state);
        },

        /**
         *  Callback when value is changed by user
         */
        userChanges: function () {
            this.valueChangedByUser = true;
        }
    });
});
