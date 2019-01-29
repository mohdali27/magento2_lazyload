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

namespace Webkul\Marketplace\Block\Adminhtml\Customer\Edit\Tab\Grid;

use Magento\Customer\Controller\RegistryConstants;

class Product extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Product\Collection
     */
    protected $_sellerProduct;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context                    $context
     * @param \Magento\Backend\Helper\Data                               $backendHelper
     * @param \Magento\Framework\Registry                                $coreRegistry
     * @param \Magento\Catalog\Model\ProductFactory                      $productFactory
     * @param \Webkul\Marketplace\Model\ResourceModel\Product\Collection $sellerProduct
     * @param \Magento\Framework\Json\EncoderInterface                   $jsonEncoder
     * @param array                                                      $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\Marketplace\Model\ResourceModel\Product\Collection $sellerProduct,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_productFactory = $productFactory;
        $this->_sellerProduct = $sellerProduct;
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('seller_product_grid');
        $this->setDefaultSort('entity_at');
        $this->setUseAjax(true);
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in adminassign flag
        if ($column->getId() == 'in_adminassign') {
            $productIds = $this->getSellerAssignedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } elseif (!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Apply various selection filters to prepare the sales order grid collection.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $this->setDefaultFilter(['in_adminassign' => 1]);

        $allOtherSellerProductIds = $this->getAllOtherSellerAssignedProducts();

        $collection = $this->_productFactory->create()->getCollection()
        ->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'price'
        );
        if (!empty($allOtherSellerProductIds)) {
            $collection->addFieldToFilter('entity_id', ['nin' => $allOtherSellerProductIds]);
        }

        $this->setCollection($collection);

        $paramData = $this->getRequest()->getParams();

        if (!isset($paramData['filter'])) {
            $productIds = $this->getSellerAssignedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
        }

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_adminassign',
            [
                'type' => 'checkbox',
                'name' => 'in_adminassign',
                'index' => 'entity_id',
                'data-form-part' => $this->getData('target_form'),
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction',
                'values' => $this->getSellerAssignedProducts()
            ]
        );
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'sortable' => true
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Product Name'),
                'index' => 'name'
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('Product SKU'),
                'index' => 'sku'
            ]
        );
        $this->addColumn(
            'price',
            [
                'header' => __('Product Price'),
                'index' => 'price',
                'type' => 'currency',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Retrieve the Url for a specified sales order row.
     *
     * @param \Magento\Sales\Model\Order|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('catalog/product/edit', ['id' => $row->getId()]);
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('marketplace/seller/product', ['_current' => true]);
    }

    /**
     * @return array
     */
    protected function getSellerAssignedProducts()
    {
        $products = $this->_sellerProduct->getAllAssignProducts(
            "`seller_id`=".(int)$this->getRequest()->getParam('id', 0)
        );
        return $products;
    }

    /**
     * @return array
     */
    protected function getAllOtherSellerAssignedProducts()
    {
        $products = $this->_sellerProduct->getAllAssignProducts(
            "`seller_id`!=".(int)$this->getRequest()->getParam('id', 0)
        );
        return $products;
    }

    /**
     * @return string
     */
    public function getSellerAssignedProductsJson()
    {
        $products = $this->getSellerAssignedProducts();
        if (!empty($products)) {
            return $this->jsonEncoder->encode($products);
        }
        return '{}';
    }
}
