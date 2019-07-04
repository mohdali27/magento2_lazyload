<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\TransferStock\External\Form;
use Magestore\InventorySuccess\Model\ResourceModel\TransferStock\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Field;
use Magestore\InventorySuccess\Model\TransferStockFactory;
use Magento\Framework\Exception\NoSuchEntityException;


class General extends AbstractDataProvider
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;


    protected $_transferStockFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse
     */
    protected $_warehouseSource;

    /**
     * Generate constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $urlBuilder
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\CollectionFactory $collectionFactory,
        UrlInterface $urlBuilder,
        TransferStockFactory $transferStockFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse $warehouseSource,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();

        $this->urlBuilder = $urlBuilder;
        $this->_transferStockFactory = $transferStockFactory;
        $this->_request = $request;
        $this->_warehouseSource = $warehouseSource;
        $this->meta = $this->prepareMeta($this->meta);

    }
    
    protected function getFieldsMap()
    {
        return [
            'init_general' =>
                [
                    'transfer_code',
                    'source_warehouse_id',
                    'des_warehouse_id',
                    'notifier_emails',
                    'reason',
                ]
            ];
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $this->loadedData = [];
        $transferStock = $this->getCurrentRequestStock();
        if ($transferStock) {
            $transferStockData = $transferStock->getData();
            $this->loadedData[$transferStock->getTransferstockId()] = $transferStockData;
        }
        return $this->loadedData;
    }


    public function getCurrentRequestStock()
    {
        $transferStock = [];
        $requestId = $this->_request->getParam($this->requestFieldName);
        if ($requestId) {
            $transferStock = $this->_transferStockFactory->create();
            $transferStock->load($requestId);
            if (!$transferStock->getTransferstockId()) {
                throw NoSuchEntityException::singleField('transferstock_id', $requestId);
            }
        }
        return $transferStock;
    }


    public function prepareMeta($meta)
    {
        $meta = array_replace_recursive($meta, $this->prepareFieldsMeta(
            $this->getFieldsMap(),
            $this->getAttributesMeta()
        ));

        return $meta;
    }


    private function prepareFieldsMeta($fieldsMap, $fieldsMeta)
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


    public function getAttributesMeta()
    {
        $result = [];
        $result['source_warehouse_id']['componentType'] = Field::NAME;
        $result['source_warehouse_id']['options'] = $this->_warehouseSource->toOptionArray();
        $result['des_warehouse_id']['componentType'] = Field::NAME;
        $result['des_warehouse_id']['options'] = $this->_warehouseSource->toOptionArray();

        $result = $this->getDefaultMetaData($result);

        return $result;
    }


    public function getDefaultMetaData($result)
    {
        $result['transferstock_code']['default'] = 'AAAA';
        return $result;
    }
}