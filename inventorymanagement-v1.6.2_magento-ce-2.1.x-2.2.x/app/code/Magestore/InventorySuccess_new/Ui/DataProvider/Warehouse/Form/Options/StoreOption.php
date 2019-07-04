<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\Warehouse\Form\Options;

use Magento\Framework\Cache\Frontend\Adapter\Zend;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;

/**
 * Class Options
 */
class StoreOption implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var \Magestore\OrderSuccess\Helper\Data
     */
    protected $helperData;
    /**
     * Constructor
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magestore\InventorySuccess\Helper\Data $helperData
    )
    {
        $this->helperData = $helperData;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $array = [];
            if ($this->helperData->checkModuleEnable('Magestore_Storepickup')) {
                $collection = \Magento\Framework\App\ObjectManager::getInstance()
                    ->create('Magestore\Storepickup\Model\ResourceModel\Store\Collection');
                foreach ($collection as $_store) {
                    $array[] = ['value' => $_store->getId(), 'label' => $_store->getStoreName()];
                }
            }
            $this->options = $array;
        }
        return $this->options;
    }
}
