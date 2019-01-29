<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Customoption
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Customoption\Plugin\Catalog\Model\Product\Option;

class SaveHandler
{
    /**
     * function to run to change the custom option value.
     *
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param array $result
     *
     * @return bool
     */
    public function beforeExecute(
        \Magento\Catalog\Model\Product\Option\SaveHandler $subject,
        $entity,
        $arguments = []
    ) {
        $options = $entity->getOptions(); 
        if ($options) {
            foreach ($options as $option) {
                $optionData = $option->getData();
                if (is_null($option->getOptionId()) && !empty($option->getProductId())) {
                    if ($option->hasValues()) {
                        if (isset($optionData['values'])) {
                            $values = $optionData['values'];
                            foreach ($option->getData('values') as $key => $value) {
                                if (isset($value['option_id']) && $value['option_id'] != "") {
                                    $values[$key]['record_id'] = $key;
                                    empty($values[$key]['is_delete']);
                                }
                            }
                            $optionData['values'] = $values;
                            $option->setData('values', $values);
                        }
                    }
                } else {
                    if ($option->hasValues()) {
                        if ($option->getData('values') !== null) {
                            foreach ($option->getData('values') as $key) {
                                if (isset($key['option_type_id'])){
                                    $optionTypeId = $key['option_type_id'];
                                    $price = $key['price'];
                                    $priceType = $key['price_type'];
                                    $this->savePrice($optionTypeId, $price, $priceType);
                                }
                            }
                        }
                    }
                }
                $option->setData($optionData);
            }
            $entity->setOptions($options);
        }
        return [$entity, $arguments];
    }

    public function savePrice($optionTypeId, $price, $priceType)
    {
        try {
            $storeId = 0;
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->create('Magento\Framework\App\ResourceConnection');
            $priceTable = $resource->getTableName('catalog_product_option_type_price');

            $select = $resource->getConnection()->select()->from(
                $priceTable,
                'option_type_id'
            )->where(
                'option_type_id = ?',
                $optionTypeId
            )->where(
                'store_id = ?',
                $storeId
            );
            if ($optionTypeId) {
                $optionTypeId = $resource->getConnection()->fetchOne($select);
                $bind = ['price' => $price, 'price_type' => $priceType];
                $where = [
                    'option_type_id = ?' => $optionTypeId,
                    'store_id = ?' => 0,
                ];
                $resource->getConnection()->update($priceTable, $bind, $where);
            }
        } catch (\Exception $e) {
          
        }
    }
}
