<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\Component\Listing\Columns\TransferStock\Request;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Actions.
 *
 * @category Magestore
 * @package  Magestore_InventorySuccess
 * @module   Inventorysuccess
 * @author   Magestore Developer
 */
class EditAction extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magestore\InventorySuccess\Model\TransferStockFactory $_transferStockFactory
     */
    protected $_transferStockFactory;


    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    protected $_editUrl = 'inventorysuccess/transferstock_request/edit';

    /**
     * Constructor.
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Magestore\InventorySuccess\Model\TransferStockFactory $transferStockFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->_transferStockFactory = $transferStockFactory;
    }

    public function prepareItemEditUrl($itemData){
        if(isset($itemData['type'])){
            switch ($itemData['type']){
                case "request":
                    $this->_editUrl = 'inventorysuccess/transferstock_request/edit';
                    break;
                case "send":
                    $this->_editUrl = 'inventorysuccess/transferstock_send/edit';
                    break;
                case "to_external":
                    $this->_editUrl = 'inventorysuccess/transferstock_external/edit/type/to_external';
                    break;
                case "from_external":
                    $this->_editUrl = 'inventorysuccess/transferstock_external/edit/from_external';
                    break;
                default:
                    $this->_editUrl = 'inventorysuccess/transferstock_request/edit';
                    break;
            }
        }
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
            $indexField = $this->getData('config/indexField');
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item[$indexField])) {
                    $this->prepareItemEditUrl($item);
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl($this->_editUrl, ['id' => $item[$indexField]]),
                        'label' => __('View')
                    ];
                }
            }
        }

        return $dataSource;
    }
}
