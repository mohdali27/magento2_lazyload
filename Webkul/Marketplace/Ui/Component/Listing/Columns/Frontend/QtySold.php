<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Ui\Component\Listing\Columns\Frontend;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory;
use Webkul\Marketplace\Helper\Data as HelperData;
use Magento\Framework\UrlInterface;

/**
 * Class QtySold.
 */
class QtySold extends Column
{
    /**
     * @var CollectionFactory
     */
    public $collectionFactory;

    /**
     * @var HelperData
     */
    public $helperData;
    
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor.
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CollectionFactory  $collectionFactory
     * @param HelperData         $helperData
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CollectionFactory $collectionFactory,
        HelperData $helperData,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->helperData = $helperData;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            $sellerId = $this->helperData->getCustomerId();
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['entity_id'])) {
                    $collectionData = $this->collectionFactory->create()
                    ->addFieldToFilter(
                        'mageproduct_id',
                        $item['entity_id']
                    )->addFieldToFilter(
                        'seller_id',
                        $sellerId
                    );
                    $data = $collectionData->getAllSoldQty();
                    if (!empty($data)) {
                        $url = $this->urlBuilder->getUrl(
                            'marketplace/order/salesdetail/',
                            [
                                'id'=>$item['entity_id']
                            ]
                        );
                        $item[$fieldName] = "<a href='".$url."'>".$data['0']['qty']."</a>";
                    } else {
                        $item[$fieldName] = 0;
                    }
                }
            }
        }

        return $dataSource;
    }
}
