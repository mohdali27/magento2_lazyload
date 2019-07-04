<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\TransferStock\Send\Form;
use Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferActivity\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\UrlInterface;

use Magestore\InventorySuccess\Model\TransferStock\TransferActivityFactory;


class ReturnStock extends AbstractDataProvider
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Framework\App\RequestInterface $request,
        CollectionFactory $collectionFactory,
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
        if($transferstock_id){
            $this->collection->addFieldToFilter("activity_type", "return");
            $this->collection->addFieldToFilter("transferstock_id", $transferstock_id);
        }
    }
}