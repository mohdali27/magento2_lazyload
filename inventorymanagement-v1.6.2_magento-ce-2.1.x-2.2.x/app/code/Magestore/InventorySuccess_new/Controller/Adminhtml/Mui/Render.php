<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Mui;

/**
 * Class Render
 */
class Render extends \Magento\Ui\Controller\Adminhtml\Index\Render
{
    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        $params = $this->_request->getParams();
        if (isset($params['filters_modifier']) && is_string($params['filters_modifier'])) {
            $filtersModifier = json_decode($params['filters_modifier'], true);
            $params['filters_modifier'] = $this->processFiltersModifier($filtersModifier);
            $this->_request->setParams($params);
        }
        return parent::execute();
    }
    
    /**
     * Process filters modifier after json_decode
     * 
     * @param array $filtersModifier
     * @return mixed[]
     */
    protected function processFiltersModifier($filtersModifier)
    {
        $result = [];
        foreach ($filtersModifier as $key => $modifier) {
            if (is_array($modifier)) {
                $modifier = $this->processFiltersModifier($modifier);
                if (!empty($modifier)) {
                    $result[$key] = $modifier;
                }
            } else {
                $result[$key] = $modifier;
            }
        }
        return $result;
    }
}
