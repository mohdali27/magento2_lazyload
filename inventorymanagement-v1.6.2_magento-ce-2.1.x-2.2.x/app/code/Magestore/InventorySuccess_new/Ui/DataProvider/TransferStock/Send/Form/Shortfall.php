<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\TransferStock\Send\Form;

use Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Field;
use Magestore\InventorySuccess\Model\TransferStock\TransferStockProductFactory;


class Shortfall extends AbstractDataProvider
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

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
        \Magento\Framework\App\RequestInterface $request,
        \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct\CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
        $this->collection = $collectionFactory->create();
        $this->prepareCollection();
    }

    public function prepareCollection()
    {
        $transferstock_id = $this->request->getParam('transferstock_id');
        $this->collection->getTransferStockProduct($transferstock_id);
        if($transferstock_id) {
             $this->collection->addFieldToFilter('transferstock_id', $transferstock_id);
             $this->collection->getSelect()->where('(qty - qty_received - qty_returned) > ? ', 0);
        }
    }
}