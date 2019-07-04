<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Warehouse;

class Options implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var null|array
     */
    protected $options;
    
    /**
     * @var bool 
     */
    protected $showAnonymousRow = false;

    /**
     * 
     * @param \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }
    
    /**
     * 
     * @return \Magestore\InventorySuccess\Model\Warehouse\Options
     */
    public function showDummyRow()
    {
        $this->showAnonymousRow = true;
        return $this;
    }

    /**
     * @return array|null
     */
    public function toOptionArray()
    {
        if (null == $this->options) {
            if($this->showAnonymousRow) {
                $this->options = [['value' => 0, 'label' => __('Select Location')]];
            } else {
                $this->options = [];
            }       
            $this->options = array_merge($this->options , $this->collectionFactory->create()->toOptionArray());
        }
        
        return $this->options;
    }

    /**
     * @return array
     */
    public function toHashOption() {
        $options = $this->collectionFactory->create()->toOptionArray();
        $data = [];
        foreach ($options as $option) {
            if($option['value'] == 0) {
                continue;
            }
            $data[$option['value']] = $option['label'];
        }
        return $data;
    }
}
