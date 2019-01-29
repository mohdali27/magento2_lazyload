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

namespace Webkul\Marketplace\Block;

/**
 * Webkul Marketplace Sellerlist Block.
 */
class Sellerlist extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory
     */
    protected $_sellerlistCollectionFactory;

    /** @var \Webkul\Marketplace\Model\Seller */
    protected $sellerList;

    /**
     * @param Context                                    $context
     * @param array                                      $data
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Framework\ObjectManagerInterface  $objectManager
     */
    public function __construct(
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Block\Product\Context $context,
        \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory $sellerlistCollectionFactory,
        array $data = []
    ) {
        $this->_sellerlistCollectionFactory = $sellerlistCollectionFactory;
        $this->_filterProvider = $filterProvider;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getSellerCollection()
    {
        if (!$this->sellerList) {
            $helper = $this->_objectManager->create(
                'Webkul\Marketplace\Helper\Data'
            );
            $paramData = $this->getRequest()->getParams();
            
            $sellerArr = [];

            $sellerProductColl = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Product'
            )
            ->getCollection()
            ->addFieldToFilter(
                'status',
                ['eq' => 1]
            )
            ->addFieldToSelect('seller_id')
            ->distinct(true);
            $sellerArr = $sellerProductColl->getAllSellerIds();

            $storeCollection = $this->_sellerlistCollectionFactory
            ->create()
            ->addFieldToSelect(
                '*'
            )
            ->addFieldToFilter(
                'seller_id',
                ['in' => $sellerArr]
            )
            ->addFieldToFilter(
                'is_seller',
                ['eq' => 1]
            )->addFieldToFilter(
                'store_id',
                $helper->getCurrentStoreId()
            )->setOrder(
                'entity_id',
                'desc'
            );
            $storeSellerIDs = $storeCollection->getAllIds();
            $storeMainSellerIDs = $storeCollection->getAllSellerIds();

            $sellerArr = array_diff($sellerArr, $storeMainSellerIDs);

            $adminStoreCollection = $this->_sellerlistCollectionFactory
            ->create()
            ->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'seller_id',
                ['in' => $sellerArr]
            );
            if (!empty($storeSellerIDs)) {
                $adminStoreCollection->addFieldToFilter(
                    'entity_id',
                    ['nin' => $storeSellerIDs]
                );
            }
            $adminStoreCollection->addFieldToFilter(
                'is_seller',
                ['eq' => 1]
            )->addFieldToFilter(
                'store_id',
                0
            )->setOrder(
                'entity_id',
                'desc'
            );
            $adminStoreSellerIDs = $adminStoreCollection->getAllIds();
            
            $allSellerIDs = array_merge($storeSellerIDs, $adminStoreSellerIDs);

            $collection = $this->_sellerlistCollectionFactory
            ->create()
            ->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'entity_id',
                ['in' => $allSellerIDs]
            )->setOrder(
                'entity_id',
                'desc'
            );

            if (isset($paramData['shop']) && $paramData['shop']) {
                $collection->addFieldToFilter(
                    [
                        "shop_title",
                        "shop_url"
                    ],
                    [
                        ["like"=>"%".$paramData['shop']."%"],
                        ["like"=>"%".$paramData['shop']."%"]
                    ]
                );
            }
            $websiteId = $helper->getWebsiteId();
            $joinTable = $this->_objectManager->create(
                'Webkul\Marketplace\Model\ResourceModel\Seller\Collection'
            )->getTable('customer_grid_flat');
            $collection->getSelect()->join(
                $joinTable.' as cgf',
                'main_table.seller_id = cgf.entity_id AND website_id= '.$websiteId
            );
            $this->sellerList = $collection;
        }

        return $this->sellerList;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getSellerCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'marketplace.seller.list.pager'
            )
            ->setAvailableLimit([4 => 4, 8 => 8, 16 => 16])
            ->setCollection(
                $this->getSellerCollection()
            );
            $this->setChild('pager', $pager);
            $this->getSellerCollection()->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Prepare HTML content.
     *
     * @return string
     */
    public function getCmsFilterContent($value = '')
    {
        $html = $this->_filterProvider->getPageFilter()->filter($value);

        return $html;
    }
}
