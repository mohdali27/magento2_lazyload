<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\Component\Listing\Columns\StockMovement;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magestore\InventorySuccess\Model\WarehouseFactory;

/**
 * Class ProductActions
 */
class Reference extends Column
{
    /**
     * @var array
     */
    protected $_stockMovementConfig;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManagerInterface;

    /**
     * Reference constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param \Magestore\InventorySuccess\Model\StockActivity\StockMovementProvider $stockMovementProvider
     * @param \Magento\Framework\ObjectManagerInterface $objectManagerInterface
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Magestore\InventorySuccess\Model\StockActivity\StockMovementProvider $stockMovementProvider,
        \Magento\Framework\ObjectManagerInterface $objectManagerInterface,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_stockMovementConfig = $stockMovementProvider->getActionConfig();
        $this->_objectManagerInterface = $objectManagerInterface;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $data = [
                    'label' => $item['action_number'],
                    'hidden' => false,
                ];
                $data = $this->_getDataUrl($data, $item);
                $item[$this->getData('name')]['edit'] = $data;
            }
        }
        return $dataSource;
    }
    
    private function _getDataUrl($data = [], $item){
        if($item['action_code']!='' && $item['action_id']!=null){
            if(isset($this->_stockMovementConfig[$item['action_code']])){
                $config = $this->_stockMovementConfig[$item['action_code']];
                $data['href'] = $this->_objectManagerInterface->create($config['class'])
                    ->getStockMovementActionUrl($item['action_id']);
            }
        }
        return $data;
    }
}
