<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\Component\Listing\Columns\TransferStock\External\From;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magestore\InventorySuccess\Model\WarehouseFactory;

/**
 * Class ProductActions
 */
class AvailableQty extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var WarehouseFactory
     */
    protected $_warehouseFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request,
        WarehouseFactory $warehouseFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->_warehouseFactory = $warehouseFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepare(){
        parent::prepare();
        $warehouseId = $this->request->getParam('warehouse_label_id');
        if($warehouseId){
            $warehouse = $this->_warehouseFactory->create()->load($warehouseId);
            $availableLabel  = 'Available Qty In '. $warehouse->getWarehouseName();
            $config = $this->getData('config');
            $config['label'] = $availableLabel;
            $this->setData('config',$config);
        }
    }
}
