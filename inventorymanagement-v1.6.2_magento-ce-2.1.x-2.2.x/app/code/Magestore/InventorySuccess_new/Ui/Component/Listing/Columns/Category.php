<?php
/**
 * Created by PhpStorm.
 * User: duongdiep
 * Date: 25/01/2017
 * Time: 08:58
 */

namespace Magestore\InventorySuccess\Ui\Component\Listing\Columns;

use Magento\Framework\Escaper;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\CategoryFactory;

/**
 * Class Address
 */
class Category extends Column
{
    /**
     * @var Escaper
     */
    protected $escaper;


    /**
     * @var ProductInterface
     */
    protected $_productFactory;

    /**
     * @var CategoryInterface
     */
    protected $_categorytFactory;


    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory,
        array $components = [],
        array $data = []
    ) {
        $this->escaper = $escaper;
        $this->_productFactory = $productFactory;
        $this->_categoryFactory = $categoryFactory;
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
                $category_name = '';
                $product = $this->_productFactory->create()->load($item['entity_id']);
                $cats = $product->getCategoryIds();
                if(count($cats) ){
                    foreach($cats as $key){
                        $_category = $this->_categoryFactory->create()->load($key);
                         $category_name .= $_category->getName()."</br>";
                    }
                }
                $item[$this->getData('name')] = html_entity_decode("<a>" . $category_name . "</a>");
            }
        }
        return $dataSource;
    }
}
