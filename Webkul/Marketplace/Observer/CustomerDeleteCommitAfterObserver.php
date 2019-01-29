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

namespace Webkul\Marketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;

/**
 * Webkul Marketplace CustomerDeleteCommitAfterObserver Observer Model.
 */
class CustomerDeleteCommitAfterObserver implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;
    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var eventManager
     */
    protected $_eventManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface       $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime     $date
     * @param \Magento\Store\Model\StoreManagerInterface      $storeManager,
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
     * @param CollectionFactory                               $collectionFactory
     * @param \Magento\Framework\Event\Manager                $eventManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Event\Manager $eventManager
    ) {
        $this->_objectManager = $objectManager;
        $this->_collectionFactory = $collectionFactory;
        $this->_productRepository = $productRepository;
        $this->messageManager = $messageManager;
        $this->_storeManager = $storeManager;
        $this->_date = $date;
        $this->_eventManager = $eventManager;
    }

    /**
     * customer Delete After event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $customer = $observer->getCustomer();
            $customerid = $customer->getId();
            $sellerId = $customerid;
            $entityId = '';
            $sellerCollection = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Seller'
            )
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                $customerid
            );
            foreach ($sellerCollection as $seller) {
                $sellerId = $seller->getSellerId();
                $entityId = $seller->getId();
                $seller->delete();
            }
            if ($sellerId) {
                $productCollection = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Product'
                )->getCollection()
                ->addFieldToFilter(
                    'seller_id',
                    $sellerId
                );
                $productIds = $productCollection->getAllIds();

                $wholedata['product_mass_delete'] = $productIds;
                $this->_eventManager->dispatch(
                    'mp_delete_product',
                    [$wholedata]
                );
                foreach ($productCollection as $productData) {
                    $product = $this->_productRepository->getById(
                        $productData->getMageproductId()
                    );
                    if ($product->getId()) {
                        $product->setStatus(
                            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED
                        );
                        $this->_productRepository->save($product);
                    }
                    $productData->delete();
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }
}
