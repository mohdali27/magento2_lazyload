<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\TransferStock\External\Form;
use Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferActivity\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\UrlInterface;

use Magestore\InventorySuccess\Model\TransferStock\TransferActivityFactory;


class ImportProduct extends AbstractDataProvider
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    protected $_transferActivityFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;
    
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        UrlInterface $urlBuilder,
        TransferActivityFactory $transferActivityFactory,
        \Magento\Framework\App\RequestInterface $request,

        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        //$this->collection->addFieldToFilter("activity_type", "delivery");
        $this->urlBuilder = $urlBuilder;
        $this->_transferActivityFactory = $transferActivityFactory;
        $this->_request = $request;
    }
}