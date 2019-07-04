<?php

namespace Magestore\InventorySuccess\Rewrite\VisualMerchandiser\Model\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var string
     */
    protected $positionCacheKey;

    /**
     * @var \Magento\VisualMerchandiser\Model\Position\Cache
     */
    protected $cache;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->cache = $this->_objectManager->create('Magestore\InventorySuccess\Rewrite\VisualMerchandiser\Model\Position\Cache');
        $this->request = $request;
        $this->collection = $collectionFactory->create()->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'attribute_set_id'
        )->addAttributeToSelect(
            'type_id'
        )->addAttributeToSelect(
            'price'
        );

        $this->collection->joinField(
            'qty',
            'cataloginventory_stock_item',
            'qty',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'left'
        );
        $this->collection->getSelect()->where('at_qty.website_id = 0');
        $this->prepareUpdateUrl();
    }

    /**
     * @param \Magento\Framework\Api\Filter $filter
     * @return void
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() != 'fulltext') {
            $this->collection->addAttributeToFilter(
                $filter->getField(),
                [$filter->getConditionType() => $filter->getValue()]
            );
        } else {
            $this->collection->addAttributeToFilter(
                [
                    ['attribute' => 'name', 'like' => "%{$filter->getValue()}%"],
                    ['attribute' => 'sku', 'like' => "%{$filter->getValue()}%"]
                ]
            );
        }
    }

    /**
     * Prepares update url
     *
     * @return void
     */
    protected function prepareUpdateUrl()
    {
        if (!isset($this->data['config']['filter_url_params'])) {
            return;
        }
        foreach ($this->data['config']['filter_url_params'] as $paramName => $paramValue) {
            if ('*' == $paramValue) {
                $paramValue = $this->request->getParam($paramName);
                $this->positionCacheKey = $paramValue;
            }

            if ($paramValue) {
                $this->data['config']['update_url'] = sprintf(
                    '%s%s/%s',
                    $this->data['config']['update_url'],
                    $paramName,
                    $paramValue
                );
            }
        }
    }

    /**
     * Sets the position values
     *
     * @return void
     */
    public function addPositionData()
    {
        $positions = $this->cache->getPositions($this->positionCacheKey);

        if ($positions === false) {
            return;
        }

        foreach ($this->collection as $item) {
            if (array_key_exists($item->getEntityId(), $positions)) {
                $item->setPosition(
                    $positions[$item->getEntityId()]
                );
                $item->setIds(
                    $item->getEntityId()
                );
            } else {
                $item->setIds(null);
                $item->setPosition(null);
            }
        }
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $this->addPositionData();
        $arrItems = [];
        $arrItems['totalRecords'] = $this->collection->getSize();
        $arrItems['items'] = [];
        $arrItems['selectedData'] = $this->cache->getPositions($this->positionCacheKey);
        $arrItems['allIds'] = $this->collection->getAllIds();

        foreach ($this->collection->getItems() as $item) {
            $arrItems['items'][] =  $item->toArray();
        }

        return $arrItems;
    }
}
