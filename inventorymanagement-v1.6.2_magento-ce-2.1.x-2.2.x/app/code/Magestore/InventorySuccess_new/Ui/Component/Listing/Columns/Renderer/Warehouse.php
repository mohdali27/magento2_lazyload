<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\Component\Listing\Columns\Renderer;

class Warehouse extends \Magestore\InventorySuccess\Ui\Component\Listing\Columns\Actions
{
    /**
     * @var string
     */
    protected $_editUrl = 'inventorysuccess/warehouse/edit';

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $indexField = $this->getData('config/indexField');
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item[$indexField])) {
                    $title = $item[$name];
                    $item[$name] = array();
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl($this->_editUrl, ['id' => $item[$indexField]]),
                        'label' => __($title)
                    ];
                }
            }
        }

        return $dataSource;
    }
}
