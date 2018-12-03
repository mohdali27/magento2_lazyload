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

class Import
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
    protected $storeIds;
    /**
     * Import constructor.
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->resource = $resource;
        $this->readAdapter = $this->resource->getConnection('core_read');
        $this->writeAdapter = $this->resource->getConnection('core_write');
        $this->request = $request;
    }

    /**
     * @param string $entity
     * @return bool|mixed
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
     * Get newest attribute group id
     * @param int $attributeSetId
     * @return string
     */
    public function getNewGroupId($attributeSetId)
    {
        $select = $this->readAdapter->select()
            ->from(
                $this->getTableName('eav_attribute_group'),
                [
                    'attribute_group_id'
                ]
            )->where('attribute_set_id = :attribute_set_id')
            ->order('attribute_group_id DESC')
            ->limit(1);
        $bind = [
            ':attribute_set_id' => $attributeSetId
        ];
        $newGroupId = $this->readAdapter->fetchOne($select, $bind);
        return $newGroupId;
    }

    /**
     * Get all attribute option ids
     * @param int $attributeId
     * @return array|null
     */
    public function getOptionIds($attributeId)
    {
        $select = $this->readAdapter->select()
            ->from(
                ['option' => $this->getTableName('eav_attribute_option')],
                ['option_id']
            )->where(
                'option.attribute_id = :attribute_id'
            )->group('option_id');
        $bind = [
            ':attribute_id' => $attributeId
        ];
        $result = $this->readAdapter->query($select, $bind);
        foreach ($result as $optionId) {
            $optionIds[] = $optionId['option_id'];
        }
        if (empty($optionIds)) {
            return [];
        }
        return $optionIds;
    }

    /**
     *
     * @param array $optionIds
     */
    public function deleteOldOptionValue($optionIds)
    {
        if (empty($optionIds)) {
            foreach ($optionIds as $optionId) {
                $condition = "option_id = $optionId";
                $select = $this->readAdapter->select()
                    ->from($this->getTableName('eav_attribute_option_value'))
                    ->where($condition);
                $this->writeAdapter->deleteFromSelect($select, $this->getTableName('eav_attribute_option_value'));
            }
        }
    }

    /**
     * @return array|bool
     */
    public function getAllStoreIds()
    {
        if (!isset($this->storeIds)) {
            try {
                $select = $this->readAdapter->select()
                    ->from(
                        $this->getTableName('store'),
                        ['store_id']
                    );
                $stores = $this->readAdapter->query($select);
                foreach ($stores as $store) {
                    $this->storeIds[]=$store['store_id'];
                }
            } catch (\Exception $e) {
                return false;
            }
        }
        return $this->storeIds;
    }
}
