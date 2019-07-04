<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\Component\Listing\Columns\Warehouse;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class ProductActions
 */
class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magestore\InventorySuccess\Model\Permission\PermissionManagement
     */
    protected $_permissionManagement;

    /**
     * @var \Magestore\InventorySuccess\Model\WarehouseFactory
     */
    protected $warehouseFactory;

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
        \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface $permissionManagement,
        \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->_permissionManagement = $permissionManagement;
        $this->warehouseFactory = $warehouseFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
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
                if($this->_permissionManagement->checkPermission(
                    'Magestore_InventorySuccess::warehouse_view',
                    $this->warehouseFactory->create()->load($item['warehouse_id'])
                )){
                    $item[$this->getData('name')]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'inventorysuccess/warehouse/edit',
                            ['id' => $item['warehouse_id']]
                        ),
                        'label' => __('View'),
                        'hidden' => false,
                    ];
                }
            }
        }

        return $dataSource;
    }
}
