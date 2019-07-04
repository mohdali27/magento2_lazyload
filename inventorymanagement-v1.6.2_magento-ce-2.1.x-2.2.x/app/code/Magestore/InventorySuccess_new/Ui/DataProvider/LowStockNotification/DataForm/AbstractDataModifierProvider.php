<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\LowStockNotification\DataForm;

/**
 * Class DataProvider
 */
abstract class AbstractDataModifierProvider extends \Magestore\InventorySuccess\Ui\DataProvider\Form\Modifier\Dynamic
{

    /**
     * @param $lable
     * @param $componentType
     * @param $visible
     * @param $dataType
     * @param $formElement
     * @param $validation
     * @param $notice
     * @return array
     */
    public function getField($lable, $componentType, $visible, $dataType, $formElement, $validation, $notice)
    {
        $container = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => $lable,
                        'componentType' => $componentType,
                        'visible' => $visible,
                        'dataType' => $dataType,
                        'formElement' => $formElement,
                        'validation' => $validation,
                        'notice' => $notice
                    ]
                ]
            ]
        ];
        return $container;
    }

    /**
     * @param $lable
     * @param $componentType
     * @param $visible
     * @param $dataType
     * @param $formElement
     * @param $validation
     * @param $notice
     * @param $elementTmpl
     * @return array
     */
    public function getModifyField($lable, $componentType, $visible, $dataType, $formElement, $validation, $notice, $elementTmpl)
    {
        $container = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => $lable,
                        'componentType' => $componentType,
                        'visible' => $visible,
                        'dataType' => $dataType,
                        'formElement' => $formElement,
                        'validation' => $validation,
                        'notice' => $notice,
                        'elementTmpl' => $elementTmpl
                    ]
                ]
            ]
        ];
        return $container;
    }

    /**
     * Prepare meta data
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta($meta)
    {
        $meta = array_replace_recursive(
            $meta,
            $this->prepareFieldsMeta(
                $this->getFieldsMap(),
                $this->getAttributesMeta()
            )
        );

        return $meta;
    }

    /**
     * Prepare fields meta based on xml declaration of form and fields metadata
     *
     * @param array $fieldsMap
     * @param array $fieldsMeta
     * @return array
     */
    public function prepareFieldsMeta($fieldsMap, $fieldsMeta)
    {
        $result = [];
        foreach ($fieldsMap as $fieldSet => $fields) {
            foreach ($fields as $field) {
                if (isset($fieldsMeta[$field])) {
                    $result[$fieldSet]['children'][$field]['arguments']['data']['config'] = $fieldsMeta[$field];
                }
            }
        }
        return $result;
    }
}
