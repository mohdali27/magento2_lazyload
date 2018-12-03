<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductAttributesImportExport
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductAttributesImportExport\Model\ResourceModel;

class Export
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var array
     */
    protected $tableNames = [];

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $readAdapter;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $writeAdapter;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var array
     */
    protected $attributeOptionIds;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    protected $productFactory;

    /**
     * Export constructor.
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Catalog\Model\ResourceModel\ProductFactory $productFactory
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $productFactory,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->productFactory = $productFactory;
        $this->resource = $resource;
        $this->readAdapter = $this->resource->getConnection('core_read');
        $this->writeAdapter = $this->resource->getConnection('core_write');
        $this->request = $request;
    }

    /**
     * @return \Zend_Db_Statement_Interface
     */
    public function getAllAttributeCollection()
    {
        $select = $this->readAdapter->select()
            ->from(
                ['main_table' => $this->getTableName('eav_attribute')]
            )
            ->join(
                ['additional_table' => $this->getTableName('catalog_eav_attribute')],
                'main_table.attribute_id = additional_table.attribute_id',
                ['*']
            )->join(
                ['entity' => $this->getTableName('eav_entity_attribute')],
                'main_table.attribute_id = entity.attribute_id',
                ['attribute_set_id', 'attribute_group_id']
            )->join(
                ['attribute_set' => $this->getTableName('eav_attribute_set')],
                'attribute_set.attribute_set_id = entity.attribute_set_id',
                ['attribute_set' => 'attribute_set_name']
            )->join(
                ['attribute_group' => $this->getTableName('eav_attribute_group')],
                'attribute_group.attribute_group_id = entity.attribute_group_id',
                ['attribute_group_name']
            )->where('main_table.entity_type_id = :entity_type_id');
        $entityTypeId = $this->productFactory->create()->getTypeId();

        if ($this->getAttributeSetToExport()!='all') {
            $select->where('attribute_set.attribute_set_id = :attribute_set_id');
            $bind = [
                ':entity_type_id' => $entityTypeId,
                ':attribute_set_id' => $this->getAttributeSetToExport()
            ];
        } else {
            $bind = [
                ':entity_type_id' => $entityTypeId
            ];
        }

        $attributeCollection = $this->readAdapter->query($select, $bind);
        return $attributeCollection;
    }

    /**
     * @return array
     */
    public function getAttributeOptionColumn()
    {
        $select = $this->readAdapter->select()
            ->from(
                ['option_value' => $this->getTableName('eav_attribute_option_value')]
            )
            ->join(
                ['option' => $this->getTableName('eav_attribute_option')],
                'option_value.option_id = option.option_id',
                ['*']
            )->order('option_value.option_id');

        $attributeOptionArr = $this->readAdapter->query($select);
        $attributeOptionIds = $this->getAttributeOptionId();
        $attributeOptionColumns = [];
        foreach ($attributeOptionArr as $attributeOption) {
            if (!isset($countStore)) {
                $countStore = 1;
            }
            foreach ($attributeOptionIds as $attributeOptionId) {
                if ($attributeOption['attribute_id']==$attributeOptionId) {
                    if (!isset($attributeOptionColumns[$attributeOptionId])) {
                        $attributeOptionColumns[$attributeOptionId] = '';
                    }
                    if ($countStore<$this->getOptionStoreCount($attributeOption['option_id'])) {
                        $attributeOptionColumns[$attributeOptionId] .= $attributeOption['store_id'];
                        $attributeOptionColumns[$attributeOptionId] .= ":" . $attributeOption['value'] . ";";
                        $countStore++;
                    } else {
                        $attributeOptionColumns[$attributeOptionId] .= $attributeOption['store_id'];
                        $attributeOptionColumns[$attributeOptionId] .= ":" . $attributeOption['value'] . "|";
                        $countStore=1;
                    }
                }
            }
        }
        return $attributeOptionColumns;
    }

    /**
     * Get value into attribtue_option_swatch column
     * @return array|null
     */
    public function getAttributeOptionSwatchColumn()
    {
        $select = $this->readAdapter->select()
            ->from(
                ['option_swatch' => $this->getTableName('eav_attribute_option_swatch')]
            )
            ->join(
                ['option' => $this->getTableName('eav_attribute_option')],
                'option_swatch.option_id = option.option_id',
                ['*']
            )->order('option_swatch.option_id');

        $attributeOptionSwatchArr = $this->readAdapter->query($select);
        $attributeOptionIds = $this->getAttributeOptionId();
        $attributeOptionSwatchColumns = [];
        foreach ($attributeOptionSwatchArr as $attributeOptionSwatch) {
            if (!isset($countStore)) {
                $countStore = 1;
            }
            foreach ($attributeOptionIds as $attributeOptionId) {
                if ($attributeOptionSwatch['attribute_id']==$attributeOptionId) {
                    if ($countStore>$this->getOptionSwatchStoreCount($attributeOptionSwatch['option_id'])) {
                        $countStore = 1;
                    }
                    if (!isset($attributeOptionSwatchColumns[$attributeOptionId])) {
                        $attributeOptionSwatchColumns[$attributeOptionId] = '';
                    }
                    if ($countStore<$this->getOptionSwatchStoreCount($attributeOptionSwatch['option_id'])) {
                        $attributeOptionSwatchColumns[$attributeOptionId] .= $attributeOptionSwatch['store_id'];
                        $attributeOptionSwatchColumns[$attributeOptionId] .= ":" . $attributeOptionSwatch['value'];
                        $attributeOptionSwatchColumns[$attributeOptionId] .= ":" . $attributeOptionSwatch['type'] . ";";
                        $countStore++;
                    } else {
                        $attributeOptionSwatchColumns[$attributeOptionId] .= $attributeOptionSwatch['store_id'];
                        $attributeOptionSwatchColumns[$attributeOptionId] .= ":" . $attributeOptionSwatch['value'];
                        $attributeOptionSwatchColumns[$attributeOptionId] .= ":" . $attributeOptionSwatch['type'] . "|";
                        $countStore=1;
                    }
                }
            }
        }
        return $attributeOptionSwatchColumns;
    }

    /**
     * Get all attribute_option_id of an option attribute
     * @return array|bool
     */
    public function getAttributeOptionId()
    {
        if (!isset($this->attributeOptionIds)) {
            try {
                $select = $this->readAdapter->select()
                    ->from(
                        $this->getTableName('eav_attribute_option'),
                        ['attribute_id']
                    )->group(['attribute_id']);
                $ids=$this->readAdapter->query($select);
                foreach ($ids as $id) {
                    $this->attributeOptionIds[]=$id['attribute_id'];
                }
            } catch (\Exception $e) {
                return false;
            }
        }
        return $this->attributeOptionIds;
    }

    /**
     * @param string $entity
     * @return mixed|null
     */
    protected function getTableName($entity)
    {
        if (!isset($this->tableNames[$entity])) {
            try {
                $this->tableNames[$entity] = $this->resource->getTableName($entity);
            } catch (\Exception $e) {
                return false;
            }
        }
        return $this->tableNames[$entity];
    }

    /**
     * Count all stores of an option id
     * @param int $optionId
     * @return string
     */
    protected function getOptionStoreCount($optionId)
    {
        $select = $this->readAdapter->select()->from(
            ['option_value' => $this->getTableName('eav_attribute_option_value')],
            ['store_count' => 'COUNT(*)']
        )->where(
            "option_value.option_id = :option_id"
        );
        $bind = [
            ':option_id' => $optionId
        ];
        $storeCount = $this->readAdapter->fetchOne($select, $bind);
        return $storeCount;
    }

    /**
     * Count all stores of an option swatch id
     * @param int $optionId
     * @return string
     */
    protected function getOptionSwatchStoreCount($optionId)
    {
        $select = $this->readAdapter->select()->from(
            ['option_value' => $this->getTableName('eav_attribute_option_swatch')],
            ['store_count' => 'COUNT(*)']
        )->where(
            "option_value.option_id = :option_id"
        );
        $bind = [
            ':option_id' => $optionId
        ];
        $storeCount = $this->readAdapter->fetchOne($select, $bind);
        return $storeCount;
    }

    /**
     * @return array|null
     */
    public function getAttributeSetToExport()
    {
        return $this->request->getParam('select-attrbute-set');
    }
}
