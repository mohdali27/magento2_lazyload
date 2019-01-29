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
namespace Webkul\Marketplace\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Framework\Registry;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Catalog\Helper\Category as CategoryHelper;
use Webkul\Marketplace\Model\SellerFactory;

class AssignCategory extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * @var \Magento\Framework\Registry
     */

    public $registry;

    /**
     * @var \Magento\Catalog\Model\Category
     */

    public $category;

    /**
     * @var \Magento\Catalog\Helper\Category
     */

    public $categoryHelper;

    /**
     * @var \Webkul\Marketplace\Model\SellerFactory
     */

    public $sellerFactory;

    const ASSIGN_CATEGORY_TEMPLATE = 'customer/assign-category.phtml';

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param Registry $registry
     * @param Magento\Catalog\Model\Category $category
     * @param CategoryHelper $categoryHelper
     * @param SellerFactory $sellerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        Registry $registry,
        \Magento\Catalog\Model\Category $category,
        CategoryHelper $categoryHelper,
        SellerFactory $sellerFactory,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->category = $category;
        $this->categoryHelper = $categoryHelper;
        $this->sellerFactory = $sellerFactory;
        parent::__construct($context, $data);
    }

    /**
     * Set template to itself.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::ASSIGN_CATEGORY_TEMPLATE);
        }

        return $this;
    }

    /**
     * getCategoriesList
     * @return boolean
     */
    public function getCategoriesList()
    {
        return $this->categoryHelper->getStoreCategories();
    }

    /**
     * hasChildren
     * @param int $categoryId
     * @return boolean
     */
    public function hasChildren($categoryId)
    {
        $category = $this->category->load($categoryId);
        $childrens = $this->category->getAllChildren($category);
        return count($childrens)-1 > 0 ? true:false;
    }

    /**
     * getSellerAllowedCategory
     * @return array $category;
     */
    public function getSellerAllowedCategory()
    {
        $sellerId = $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $seller = $this->sellerFactory->create()
            ->getCollection()
            ->addFieldToFilter('seller_id', $sellerId)
            ->addFieldToFilter('store_id', 0)
            ->setPageSize(1)
            ->getFirstItem();
        $category = [];
        if ($seller->getEntityId()) {
            $category = $seller->getAllowedCategories() ? explode(',', $seller->getAllowedCategories()) :[];
        }
        return $category;
    }
}
