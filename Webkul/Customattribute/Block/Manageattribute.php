<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Customattribute
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Customattribute\Block;

use Magento\Customer\Model\Session;

/**
 * Webkul Customattribute Manage Attribute Block.
 *
 * @category    Webkul
 * @author      Webkul Software Private Limited
 */
class Manageattribute extends \Magento\Framework\View\Element\Template
{

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection
     */
    protected $_attributeGroupCollection;

    protected $_productAttributeCollection;

    protected $_session;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;
    /**
     * Websites cache.
     *
     * @var array
     */
    protected $_websites;

    /**
     * @param Context                                                          $context
     * @param array                                                            $data
     * @param \Magento\Framework\ObjectManagerInterface                        $objectManager
     * @param \Magento\Customer\Model\Session                                  $customerSession
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface                       $storeManager
     * @param Product                                                          $product
     * @param Category                                                         $category
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $attributeGroup,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $productAttribute,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_objectManager = $objectManager;
        $this->customerSession = $customerSession;
        $this->_attributeGroupCollection = $attributeGroup;
        $this->_productAttributeCollection = $productAttribute;
        $this->_storeManager = $context->getStoreManager();
        $this->_directoryHelper = $directoryHelper;
        parent::__construct($context, $data);
    }

    public function getWysiwygConfig()
    {
        $config = $this->_wysiwygConfig->getConfig();
        $config = json_encode($config->getData());
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getAttributeSet()
    {
        return $this->customerSession->getAttributeSet();
    }
    /**
     * collect all custom attribute if status visible
     * @param  int $attributeSetId
     * @return \Webkul\Customattribute\Model\Manageattribute $readresult
     */
    public function getFrontShowAttributes($attributeSetId)
    {
        $attributes = [];
        $groups = $this->_attributeGroupCollection->create()
            ->setAttributeSetFilter($attributeSetId)
            ->setSortOrder()
            ->load();
        $attributeids = [];
        foreach ($groups as $node) {
            $nodeChildren = $this->_productAttributeCollection->create()
                ->setAttributeGroupFilter($node->getId())
                ->addVisibleFilter()
                ->load();
            if ($nodeChildren->getSize() > 0) {
                foreach ($nodeChildren->getItems() as $child) {
                    array_push($attributeids, $child->getAttributeId());
                }
            }
        }
        
        $readresult = $this->_objectManager->create('Webkul\Customattribute\Model\Manageattribute')
            ->getCollection()
            ->addFieldToFilter('attribute_id', ['in' => $attributeids])
            ->addFieldToFilter('status', ['eq' => 1]);

        return $readresult;
    }

    public function getCatalogResourceEavAttribute($id)
    {
        return $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute')->load($id);
    }

    public function getCustomerGroupCollection()
    {
        return $this->_objectManager->create('Magento\Customer\Model\Group')->getCollection();
    }

    public function getWebsites()
    {
        if ($this->_websites !== null) {
            return $this->_websites;
        }

        $this->_websites = [
            0 => ['name' => __('All Websites'), 'currency' => $this->_directoryHelper->getBaseCurrencyCode()],
        ];
        /*if (!$this->isScopeGlobal()) {*/
            $websites = $this->_storeManager->getWebsites();
        foreach ($websites as $website) {
            /* @var $website \Magento\Store\Model\Website */
                $this->_websites[$website->getId()] = [
                    'name' => $website->getName(),
                    'currency' => $website->getBaseCurrencyCode(),
                ];
        }
        /*}*/
        return $this->_websites;
    }

    public function convertCurrency($price, $toCurrency = null)
    {
        return $this->_objectManager->create('Magento\Directory\Model\Currency')->convert($price, $toCurrency);
    }

    public function getProductCollection($productId)
    {
        return $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
    }

    public function getAjaxCheckUrl()
    {
        return $this->getUrl('customattribute/product/changeset', ['_current' => true]);
    }
}
