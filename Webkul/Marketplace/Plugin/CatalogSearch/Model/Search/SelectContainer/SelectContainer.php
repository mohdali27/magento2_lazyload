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
namespace Webkul\Marketplace\Plugin\CatalogSearch\Model\Search\SelectContainer;

class SelectContainer
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_mpProductCollectionFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * Initialize dependencies
     *
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $mpProductCollectionFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $mpProductCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_mpHelper = $mpHelper;
        $this->_mpProductCollectionFactory = $mpProductCollectionFactory;
        $this->_resource = $resource;
        $this->_request = $request;
    }

    /**
     * beforeUpdateSelect plugin
     *
     * @param \Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainer $subject
     * @param \Magento\Framework\DB\Select $select
     * @return void
     */
    public function beforeUpdateSelect(
        \Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainer $subject,
        $select
    ) {
        try {
            if (!$this->_mpHelper->allowSellerFilter()) {
                return [$select];
            }

            $requestVar = $this->_mpHelper->getRequestVar();
            $filter = trim($this->_request->getParam($requestVar));
            if ($filter != "") {
                $productTable = $this->_resource->getTableName('marketplace_product');
                if ($filter == $this->_mpHelper::MARKETPLACE_ADMIN_URL) {
                    $select = $select->where("search_index.entity_id not in (select mageproduct_id from $productTable)");
                } else {
                    $sellerProductCollection = $this->_mpProductCollectionFactory->create();
                    $sellerTable = $this->_resource->getTableName('marketplace_userdata');
                    $fields = ['shop_url'];
                    $sellerProductCollection->getSelect()->join($sellerTable.' as seller', 'seller.seller_id = main_table.seller_id', $fields);
                    $sellerProductCollection->getSelect()->where("seller.shop_url = '".$filter."'");
                    $sellerProductCollection->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns('main_table.mageproduct_id');
                    $sellerProductCollection->getSelect()->group("main_table.mageproduct_id");
                    $query = $sellerProductCollection->getSelect()->__toString();
                    $select = $select->where("search_index.entity_id in ($query)");
                }
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return [$select];
    }
}
