/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2019 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

pimcore.registerNS('coreshop.product.storeValues.builder');
coreshop.product.storeValues.builder = Class.create({

    data: null,
    store: null,
    fieldConfig: null,
    form: null,
    unitStore: null,
    additionalUnitCounter: 0,
    unitStoresInitialized: false,
    dirty: false,

    initialize: function (fieldConfig, store, data) {

        this.additionalUnitCounter = 0;
        this.fieldConfig = fieldConfig;
        this.store = store;
        this.data = data;
        this.unitStore = pimcore.globalmanager.get('coreshop_product_units');
        this.dirty = false;

        this.setupForm();

    },

    setupForm: function () {
        this.form = new Ext.form.Panel({
            closable: false
        });

        this.getItems();
    },

    getItems: function () {

        this.form.add([
            this.getPriceField()
        ]);

        if (this.unitStore.isLoaded()) {
            this.form.add(this.getUnitForm());
        } else {
            this.unitStore.load(function (store) {
                this.form.add(this.getUnitForm());
            }.bind(this));
        }
    },

    getForm: function () {
        return this.form;
    },

    getUnitForm: function () {

        // do not show extra unit fields if no units are available.
        if (this.unitStore.getRange().length === 0) {
            return [];
        }

        return [
            this.getDefaultUnitDefinitionField(),
            this.getAdditionalUnitDefinitionsField()
        ]
    },

    getDataValue: function (key) {

        var data, values;

        data = this.data !== null && Ext.isObject(this.data) ? this.data : null;
        if (data === null) {
            return null;
        }

        values = data.values !== null && Ext.isObject(data.values) ? data.values : null;
        if (values === null) {
            return null;
        }

        if (values.hasOwnProperty(key)) {
            return values[key];
        }

        return null;
    },

    getPriceField: function () {

        var price = this.getDataValue('price'),
            priceField = new Ext.form.NumberField({
                fieldLabel: t('coreshop_store_values_store_price'),
                name: 'price',
                componentCls: 'object_field',
                labelWidth: 250,
                minValue: 0,
                value: 0
            });

        if (price !== null) {
            priceField.setValue(price / 100);
            priceField.setFieldLabel(priceField.fieldLabel + ' (' + this.data.currencySymbol + ')');
        }

        if (this.fieldConfig.width) {
            priceField.setWidth(this.fieldConfig.width + priceField.labelWidth);
        } else {
            priceField.setWidth(350 + priceField.labelWidth);
        }

        if (is_numeric(this.fieldConfig['minValue'])) {
            priceField.setMinValue(this.fieldConfig.minValue);
        }

        if (is_numeric(this.fieldConfig['maxValue'])) {
            priceField.setMaxValue(this.fieldConfig.maxValue);
        }

        return priceField;
    },

    getDefaultUnitDefinitionField: function () {

        var defaultUnitDefinition = this.getDataValue('defaultUnitDefinition'),
            hasId = defaultUnitDefinition !== null && defaultUnitDefinition.hasOwnProperty('id') && defaultUnitDefinition.id !== null,
            unitFieldForm = this.getUnitFormFields({
                unitName: 'defaultUnitDefinition.unit',
                unitLabel: 'coreshop_store_values_unit_default_type',
                unitValue: defaultUnitDefinition !== null ? defaultUnitDefinition.unit.id : this.getDefaultUnitStoreValue(),
                precisionName: 'defaultUnitDefinition.precision',
                precisionLabel: 'coreshop_store_values_unit_precision',
                precisionValue: defaultUnitDefinition !== null ? defaultUnitDefinition.precision : 0,
            }, false);

        // add id field if available
        if (hasId === true) {
            unitFieldForm.push({
                xtype: 'hidden',
                name: 'defaultUnitDefinition.id',
                value: defaultUnitDefinition.id
            });
        }

        return Ext.create('Ext.form.Panel', {
            width: 950,
            items: [
                {
                    xtype: 'fieldset',
                    title: t('coreshop_store_values_default_unit_headline'),
                    layout: 'hbox',
                    itemCls: 'object_field',
                    items: unitFieldForm
                }
            ]
        });
    },

    getAdditionalUnitDefinitionsField: function () {

        var additionalUnitData = this.getDataValue('unitDefinitions'), fieldSet;

        fieldSet = new Ext.form.FieldSet({
            title: t('coreshop_store_values_additional_units_headline'),
            collapsible: false,
            autoHeight: true,
            width: 950,
            style: 'margin-top: 20px;',
            itemId: 'additional-units-fieldset',
            listeners: {
                afterrender: function () {
                    this.checkAddUnitBlockAvailability(fieldSet);
                    this.adjustUnitStores(true);
                    this.adjustAdditionalUnitLabel();
                }.bind(this)
            },
            items: [{
                xtype: 'toolbar',
                style: 'margin-bottom: 10px; padding: 5px;',
                height: 50,
                items: ['->', {
                    xtype: 'button',
                    iconCls: 'pimcore_icon_add',
                    itemId: 'additional-unit-add-button',
                    handler: function (b) {
                        var fieldSet = b.up('fieldset');
                        this.addAdditionalUnitField(fieldSet, null);
                        this.checkAddUnitBlockAvailability(fieldSet);
                        this.adjustUnitStores(false);
                        this.adjustAdditionalUnitLabel();
                    }.bind(this)
                }]
            }]
        });

        if (additionalUnitData !== null && Ext.isArray(additionalUnitData)) {
            Ext.Array.each(additionalUnitData, function (unit) {
                this.addAdditionalUnitField(fieldSet, unit);
            }.bind(this));
        }

        return fieldSet;
    },

    addAdditionalUnitField: function (fieldSet, data) {

        this.additionalUnitCounter++;

        var hasId = data !== null && data.hasOwnProperty('id') && data.id !== null,
            compositeField,
            unitFieldForm = this.getUnitFormFields({
                unitName: 'additionalUnit.' + this.additionalUnitCounter + '.unit',
                unitLabel: 'coreshop_store_values_unit_type',
                unitValue: data !== null && Ext.isObject(data) ? data.unit.id : null,
                precisionName: 'additionalUnit.' + this.additionalUnitCounter + '.precision',
                precisionLabel: 'coreshop_store_values_unit_precision',
                precisionValue: data !== null ? data.precision : 0,
                conversionRateName: 'additionalUnit.' + this.additionalUnitCounter + '.conversionRate',
                conversionRateLabel: 'coreshop_store_values_unit_conversion_rate',
                conversionRateValue: data !== null ? data.conversionRate : 0,
                priceName: 'additionalUnit.' + this.additionalUnitCounter + '.price',
                priceLabel: 'coreshop_store_values_unit_price',
                priceValue: data !== null ? (data.price / 100) : 0,
            }, true, hasId);

        compositeField = new Ext.form.FieldContainer({
            layout: 'hbox',
            hideLabel: true,
            itemCls: 'object_field additional-unit-field-container',
            items: unitFieldForm
        });

        compositeField.add({xtype: 'tbfill'});
        compositeField.add({
            xtype: 'button',
            itemId: 'additional-unit-delete-button',
            iconCls: 'pimcore_icon_delete',
            handler: function (compositeField, el) {
                Ext.MessageBox.confirm(t('info'), t('coreshop_store_values_additional_unit_delete_confirm'), function (buttonValue) {
                    if (buttonValue !== 'yes') {
                        return;
                    }
                    fieldSet.remove(compositeField);
                    this.dirty = true;
                    this.checkAddUnitBlockAvailability(fieldSet);
                    this.adjustUnitStores(false);
                }.bind(this));

            }.bind(this, compositeField)
        });

        // add id field if available
        if (hasId === true) {
            compositeField.add({
                xtype: 'hidden',
                name: 'additionalUnit.' + this.additionalUnitCounter + '.id',
                value: data.id
            });
        }

        fieldSet.add(compositeField);
    },

    adjustUnitStores: function (initializing) {

        var recheck = false,
            combos,
            additionalUnitCombos = this.form.query('combo[itemCls~=unit-store][cls!=default-unit-store]'),
            defaultUnitDefinitionCombo = this.form.query('combo[cls~=default-unit-store]');

        // default unit store needs to be last!
        combos = Ext.Array.merge(additionalUnitCombos, defaultUnitDefinitionCombo);

        Ext.Array.each(combos, function (combo) {

            var disallowed = [], clonedStore;

            Ext.Array.each(combos, function (subCombo) {
                if (combo.getName() !== subCombo.getName()) {
                    if (subCombo.getValue() === null) {
                        recheck = true;
                    }
                    disallowed.push(subCombo.getValue());
                }
            }.bind(this));

            if (combo.readOnly === true) {
                combo.setStore(this.unitStore);
            } else {
                clonedStore = this.cloneStore(this.unitStore, disallowed);
                combo.setStore(clonedStore);
                // current combo value is not allowed anymore
                if (disallowed.indexOf(combo.getValue()) !== -1 || combo.getValue() === null) {
                    combo.suspendEvents();
                    combo.setValue(clonedStore.first());
                    combo.resumeEvents(true);
                }
            }

            // finally: adjust precision default:
            this.adjustDefaultPrecision(combo, combo.getName() !== 'defaultUnitDefinition.unit');

        }.bind(this));

        if (initializing === true) {
            this.unitStoresInitialized = true;
        }

        if (recheck === true) {
            this.adjustUnitStores(false);
        }
    },

    adjustDefaultPrecision: function (combo, isAdditional) {
        var unitRecord = this.unitStore.getById(combo.getValue()),
            precisionField = combo.up(isAdditional ? 'fieldcontainer' : 'fieldset').query('numberfield[itemCls="unit-precision"]')[0];
        if (!unitRecord) {
            return;
        }

        precisionField.setValue(unitRecord.get('defaultPrecision'));

    },

    adjustAdditionalUnitLabel: function () {

        var unitData,
            labelText,
            defaultUnitDefinitionStore = this.form.query('combo[cls~=default-unit-store]')[0],
            additionalUnitLabels = this.form.getComponent('additional-units-fieldset').query('label[itemCls~=conversion-rate-label]'),
            defaultUnitDefinitionStoreValue = defaultUnitDefinitionStore.getValue();

        if (!defaultUnitDefinitionStoreValue) {
            labelText = '--';
        } else {
            unitData = this.unitStore.getById(defaultUnitDefinitionStoreValue);
            labelText = unitData.get('name');
        }

        Ext.Array.each(additionalUnitLabels, function (additionalUnitLabel) {
            additionalUnitLabel.setText(labelText);
        });
    },

    checkAddUnitBlockAvailability: function (comp) {
        var unitDefinitions = comp.query('fieldcontainer'),
            addButton = comp.query('button[itemId="additional-unit-add-button"]')[0];
        // -1 = default unit store cannot be selected
        addButton.setVisible(unitDefinitions.length < this.unitStore.getRange().length - 1);
    },

    cloneStore: function (store, disallowed) {
        var records = [];
        store.each(function (r) {
            if (disallowed.indexOf(r.get('id')) === -1) {
                records.push(r.copy());
            }
        });

        var store2 = new Ext.data.Store({
            recordType: store.recordType
        });

        store2.add(records);

        return store2;
    },

    getDefaultUnitStoreValue: function () {
        if (this.unitStore.isLoaded()) {
            return this.unitStore.first().get('id');
        }

        return null;
    },

    getUnitFormFields: function (data, extended, hasId) {

        var fields = [
            {
                xtype: 'combo',
                fieldLabel: t(data.unitLabel),
                name: data.unitName,
                labelWidth: 80,
                store: null,
                triggerAction: 'all',
                itemCls: 'unit-store',
                cls: (data.unitName === 'defaultUnitDefinition.unit' ? 'default-unit-store' : ''),
                typeAhead: false,
                editable: false,
                forceSelection: true,
                queryMode: 'local',
                displayField: 'name',
                valueField: 'id',
                value: data.unitValue,
                maxWidth: data.unitName === 'defaultUnitDefinition.unit' ? 250 : 200,
                readOnly: hasId,
                listeners: {
                    change: function (comp, value) {
                        if (comp.getName() === 'defaultUnitDefinition.unit' && value) {
                            this.adjustAdditionalUnitLabel();
                        }
                        if (this.unitStoresInitialized === true) {
                            this.adjustUnitStores();
                        }

                        this.adjustDefaultPrecision(comp, comp.getName() !== 'defaultUnitDefinition.unit');

                    }.bind(this)
                }
            },
            {
                xtype: 'numberfield',
                fieldLabel: t(data.precisionLabel),
                name: data.precisionName,
                itemCls: 'unit-precision',
                labelWidth: 80,
                minValue: 0,
                value: data.precisionValue,
                maxWidth: 160,
            }
        ];

        if (extended === true) {

            fields.push({
                xtype: 'numberfield',
                fieldLabel: t(data.priceLabel),
                name: data.priceName,
                labelWidth: 70,
                minValue: 0,
                value: data.priceValue,
                maxWidth: 170,
            });

            fields.push({
                xtype: 'numberfield',
                fieldLabel: t(data.conversionRateLabel),
                name: data.conversionRateName,
                labelWidth: 110,
                minValue: 0,
                value: data.conversionRateValue,
                decimalPrecision: 2,
                maxWidth: 220,
            });

            fields.push({
                xtype: 'label',
                text: 'item',
                itemCls: 'conversion-rate-label',
                style: 'margin: 7px 0 0 -4px;'
            });
        }

        return fields;

    },

    postSaveObject: function (object, refreshedData) {

        if (Ext.isObject(refreshedData) && Ext.isObject(refreshedData.values)) {
            this.data.values = refreshedData.values;
        }

        this.dirty = false;

        this.form.getForm().getFields().each(function (item) {
            item.resetOriginalValue();
        });
    },

    isDirty: function () {

        if (this.dirty === true) {
            return true;
        }

        if (this.form.getForm().isDirty()) {
            return true;
        }

        return false;
    },

    getValues: function () {
        var formValues = this.form.getForm().getFieldValues();
        if (this.getDataValue('id') !== null) {
            formValues['id'] = this.getDataValue('id');
        }

        return formValues;
    }
});