<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Rewrite\ImportExport\Block\Adminhtml\Import\Edit;

use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * Import edit form block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Form extends \Magento\ImportExport\Block\Adminhtml\Import\Edit\Form
{

    /**
     * Add fieldsets
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function _prepareForm()
    {
        parent::_prepareForm();
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('adminhtml/*/validate'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );

        // base fieldset
        $fieldsets['base'] = $form->addFieldset('base_fieldset', ['legend' => __('Import Settings')]);
        $fieldsets['base']->addField(
            'entity',
            'select',
            [
                'name' => 'entity',
                'title' => __('Entity Type'),
                'label' => __('Entity Type'),
                'required' => true,
                'onchange' => 'varienImport.handleEntityTypeSelector();',
                'values' => $this->_entityFactory->create()->toOptionArray(),
                'after_element_html' => $this->getDownloadSampleFileHtml(),
            ]
        );

        // add behaviour fieldsets
        $uniqueBehaviors = $this->_importModel->getUniqueEntityBehaviors();
        foreach ($uniqueBehaviors as $behaviorCode => $behaviorClass) {
            $fieldsets[$behaviorCode] = $form->addFieldset(
                $behaviorCode . '_fieldset',
                ['legend' => __('Import Behavior'), 'class' => 'no-display']
            );
            /** @var $behaviorSource \Magento\ImportExport\Model\Source\Import\AbstractBehavior */
            $fieldsets[$behaviorCode]->addField(
                $behaviorCode,
                'select',
                [
                    'name' => 'behavior',
                    'title' => __('Import Behavior'),
                    'label' => __('Import Behavior'),
                    'required' => true,
                    'disabled' => true,
                    'values' => $this->_behaviorFactory->create($behaviorClass)->toOptionArray(),
                    'class' => $behaviorCode,
                    'onchange' => 'varienImport.handleImportBehaviorSelector();',
                    'note' => ' ',
                ]
            );
            $fieldsets[$behaviorCode]->addField(
                $behaviorCode . \Magento\ImportExport\Model\Import::FIELD_NAME_VALIDATION_STRATEGY,
                'select',
                [
                    'name' => \Magento\ImportExport\Model\Import::FIELD_NAME_VALIDATION_STRATEGY,
                    'title' => __(' '),
                    'label' => __(' '),
                    'required' => true,
                    'class' => $behaviorCode,
                    'disabled' => true,
                    'values' => [
                        ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_STOP_ON_ERROR => 'Stop on Error',
                        ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_SKIP_ERRORS => 'Skip error entries'
                    ],
                    'after_element_html' => $this->getDownloadSampleFileHtml(),
                ]
            );
            $fieldsets[$behaviorCode]->addField(
                $behaviorCode . '_' . \Magento\ImportExport\Model\Import::FIELD_NAME_ALLOWED_ERROR_COUNT,
                'text',
                [
                    'name' => \Magento\ImportExport\Model\Import::FIELD_NAME_ALLOWED_ERROR_COUNT,
                    'label' => __('Allowed Errors Count'),
                    'title' => __('Allowed Errors Count'),
                    'required' => true,
                    'disabled' => true,
                    'value' => 10,
                    'class' => $behaviorCode . ' validate-number validate-greater-than-zero input-text',
                    'note' => __(
                        'Please specify number of errors to halt import process'
                    ),
                ]
            );
            $fieldsets[$behaviorCode]->addField(
                $behaviorCode . '_' . \Magento\ImportExport\Model\Import::FIELD_FIELD_SEPARATOR,
                'text',
                [
                    'name' => \Magento\ImportExport\Model\Import::FIELD_FIELD_SEPARATOR,
                    'label' => __('Field separator'),
                    'title' => __('Field separator'),
                    'required' => true,
                    'disabled' => true,
                    'class' => $behaviorCode,
                    'value' => ',',
                ]
            );
            $fieldsets[$behaviorCode]->addField(
                $behaviorCode . \Magento\ImportExport\Model\Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR,
                'text',
                [
                    'name' => \Magento\ImportExport\Model\Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR,
                    'label' => __('Multiple value separator'),
                    'title' => __('Multiple value separator'),
                    'required' => true,
                    'disabled' => true,
                    'class' => $behaviorCode,
                    'value' => Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR,
                ]
            );
        }

        // fieldset for file uploading
        /**
         * Get real warehouse id for comment
         */
        $warehouseCollection = \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection')
            ->setOrder('warehouse_id', 'ASC')
            ->setPageSize(2);
        $warehouseIds = [];
        foreach ($warehouseCollection as $warehouse) {
            $warehouseIds[] = $warehouse->getId();
        }
        switch (count($warehouseIds)) {
            case 0:
                array_unshift($warehouseIds, 2);
            case 1:
                array_unshift($warehouseIds, 1);
        }
        
        $fieldsets['upload'] = $form->addFieldset(
            'upload_file_fieldset',
            ['legend' => __('File to Import'), 'class' => 'no-display']
        );
        $content = __('You need to add more column into CSV file such as:').
            ('<b>').
            " qty_$warehouseIds[0], qty_$warehouseIds[1], location_$warehouseIds[0], location_$warehouseIds[1], ".
            __('etc').
            ('</b>').
            __('<br />').
            "qty_$warehouseIds[0]: ".
            __('value of this column will be updated to qty_in_warehouse of item in Location ID').
            " #$warehouseIds[0]".
            ('<br/>').
            "qty_$warehouseIds[1]: ".
            __('value of this column will be updated to qty_in_warehouse of item in Location ID').
            " #$warehouseIds[1]".
            ('<br/>').
            "location_$warehouseIds[0]: ".
            __('value of this column will be updated to shelf_location of item in Location ID').
            " #$warehouseIds[0]".
            ('<br/>').
            "location_$warehouseIds[1]: ".
            __('value of this column will be updated to shelf_location of item in Location ID').
            " #$warehouseIds[1]".
            ('<br/>');
        $fieldsets['upload']->addField(
            \Magento\ImportExport\Model\Import::FIELD_NAME_SOURCE_FILE,
            'file',
            [
                'name' => \Magento\ImportExport\Model\Import::FIELD_NAME_SOURCE_FILE,
                'label' => __('Select File to Import'),
                'title' => __('Select File to Import'),
                'required' => true,
                'class' => 'input-file',
                'after_element_html' => '<div id="test"></div>
                    <script type="text/javascript">
                        var entity = document.getElementById("entity");
                        var content = document.getElementById("test");
                        document.getElementById("entity").onclick = function () {
                            if (entity.value == "catalog_product") {
                                content.innerHTML = "'.$content.'";
                            } else {
                                content.innerHTML = "";
                            }
                        }
                    </script>
                '
            ]
        );
        $fieldsets['upload']->addField(
            \Magento\ImportExport\Model\Import::FIELD_NAME_IMG_FILE_DIR,
            'text',
            [
                'name' => \Magento\ImportExport\Model\Import::FIELD_NAME_IMG_FILE_DIR,
                'label' => __('File Directory'),
                'title' => __('File Directory'),
                'required' => false,
                'class' => 'input-text',
                'note' => __(
                    'For Type "Local Server" use relative path to Magento installation,
                                e.g. var/export, var/import, var/export/some/dir'
                ),
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);
    }
}
