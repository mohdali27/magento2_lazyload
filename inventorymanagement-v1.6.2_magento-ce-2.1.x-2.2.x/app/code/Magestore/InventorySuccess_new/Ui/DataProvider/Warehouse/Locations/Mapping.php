<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\Warehouse\Locations;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magestore\InventorySuccess\Model\ResourceModel\WarehouseLocationMap\CollectionFactory;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\DynamicRows;
use Magento\Framework\Module\Manager;

/**
 * Class Mapping
 * @package Magestore\InventorySuccess\Ui\DataProvider\Warehouse\Locations
 */
class Mapping extends AbstractDataProvider
{
    /**
     * @var mixed
     */
    protected $_moduleManager;

    /**
     * @var array
     */
    protected $dataMapping;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var Modifier
     */
    protected $dataProvider;

    /**
     * Mapping constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Collection $collection
     * @param Warehouse $warehouseOptions
     * @param Location $locationOptions
     * @param Manager $moduleManager
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collection,
        Manager $moduleManager,
        Modifier\ModifierFormDataProvider $dataProvider,
        array $meta = [],
        array $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection->create();
        $this->dataMapping = $collection->create();
        $this->_moduleManager = $moduleManager;
        $this->dataProvider = $dataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (!$this->_moduleManager->isOutputEnabled('Magestore_Webpos')) {
            return [];
        }
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $this->loadedData['']['links']['mapping'] = $this->dataMapping->joinLocationCollection()->getData();
        $this->loadedData['']['location'] = $this->collection->getLocationCollection()->getData();
        return $this->loadedData;
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        if (!$this->_moduleManager->isOutputEnabled('Magestore_Webpos')) {
            return $meta;
        }
        $meta = $this->dataProvider->modifyMeta($meta);
        return $meta;
    }
}