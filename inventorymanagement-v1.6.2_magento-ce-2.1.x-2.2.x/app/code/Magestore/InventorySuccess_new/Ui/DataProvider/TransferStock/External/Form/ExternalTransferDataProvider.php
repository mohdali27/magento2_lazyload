<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\TransferStock\External\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\UrlInterface;
use Magestore\InventorySuccess\Model\ResourceModel\TransferStock\CollectionFactory;
use Magestore\InventorySuccess\Model\TransferStockFactory;
use Magento\Framework\Phrase;
use Magento\Ui\Component\Form;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\Component\Modal;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\DynamicRows;

class ExternalTransferDataProvider extends AbstractDataProvider
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var TransferStockFactory
     */
    protected $_transferStockFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magestore\InventorySuccess\Model\Source\Adminhtml\Warehouse
     */
    protected $_warehouseSource;

    /**
     * @var PoolInterface
     */
    private $pool;


 
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        PoolInterface $pool,
        \Magestore\InventorySuccess\Model\TransferStockFactory $transferStockFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->pool = $pool;
        $this->_transferStockFactory = $transferStockFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $this->data = $modifier->modifyData($this->data);
        }

       // \Zend_Debug::dump($this->data);die();
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }
        //\Zend_Debug::dump($meta);die();
        return $meta;
    }

}