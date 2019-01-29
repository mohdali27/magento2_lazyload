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

namespace Webkul\Marketplace\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;

/**
 * Class MassApprove.
 */
class MassApprove extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;
    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $_productPriceIndexerProcessor;

    /**
     * @param Context                                     $context
     * @param Filter                                      $filter
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime          $dateTime
     * @param CollectionFactory                           $collectionFactory
     * @param Processor                                   $productPriceIndexerProcessor
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        CollectionFactory $collectionFactory,
        Processor $productPriceIndexerProcessor
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_productRepository = $productRepository;
        $this->_date = $date;
        $this->dateTime = $dateTime;
        $this->_productPriceIndexerProcessor = $productPriceIndexerProcessor;
    }

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $productIds = $collection->getAllIds();
        $allStores = $this->_storeManager->getStores();
        $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;

        $sellerProduct = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Product'
        )->getCollection();

        $coditionArr = [];
        foreach ($productIds as $key => $id) {
            $condition = "`mageproduct_id`=".$id;
            array_push($coditionArr, $condition);
        }
        $coditionData = implode(' OR ', $coditionArr);

        $sellerProduct->setProductData(
            $coditionData,
            ['status' => $status, 'seller_pending_notification' => 1, 'is_approved' => 1]
        );
        foreach ($allStores as $eachStoreId => $storeId) {
            $this->_objectManager->get(
                'Magento\Catalog\Model\Product\Action'
            )->updateAttributes($productIds, ['status' => $status], $storeId->getId());
        }
        $this->_objectManager->get(
            'Magento\Catalog\Model\Product\Action'
        )->updateAttributes($productIds, ['status' => $status], 0);

        $this->_productPriceIndexerProcessor->reindexList($productIds);

        $this->_objectManager->get(
            'Magento\Catalog\Model\Indexer\Product\Eav\Processor'
        )->reindexList($productIds);

        $sellerProductModel = $this->_objectManager->get(
            'Webkul\Marketplace\Model\Product'
        );
        $magentoProductModel = $this->_objectManager->get(
            'Magento\Catalog\Model\Product'
        );
        $customerModel = $this->_objectManager->get('Magento\Customer\Model\Customer');

        $catagoryModel = $this->_objectManager->get('Magento\Catalog\Model\Category');

        $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');

        foreach ($collection as $item) {
            $this->_objectManager->create(
                'Webkul\Marketplace\Helper\Notification'
            )->saveNotification(
                \Webkul\Marketplace\Model\Notification::TYPE_PRODUCT,
                $item->getId(),
                $item->getMageproductId()
            );
            $pro = $sellerProductModel->load($item->getId());
            $productModel = $magentoProductModel->load($item->getMageproductId());
            $catarray = $productModel->getCategoryIds();
            $categoryname = '';
            foreach ($catarray as $keycat) {
                $categoriesy = $catagoryModel->load($keycat);
                if ($categoryname == '') {
                    $categoryname = $categoriesy->getName();
                } else {
                    $categoryname = $categoryname.','.$categoriesy->getName();
                }
            }
            $adminStoreEmail = $helper->getAdminEmailId();
            $adminEmail = $adminStoreEmail ? $adminStoreEmail : $helper->getDefaultTransEmailId();
            $adminUsername = 'Admin';

            $seller = $customerModel->load(
                $item->getSellerId()
            );

            $emailTemplateVariables = [];
            $emailTemplateVariables['myvar1'] = $productModel->getName();
            $emailTemplateVariables['myvar2'] = $productModel->getDescription();
            $emailTemplateVariables['myvar3'] = $productModel->getPrice();
            $emailTemplateVariables['myvar4'] = $categoryname;
            $emailTemplateVariables['myvar5'] = $seller->getname();
            $emailTemplateVariables['myvar6'] =
            'I would like to inform you that your product has been approved.';

            $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
            $receiverInfo = [
                'name' => $seller->getName(),
                'email' => $seller->getEmail(),
            ];
            $this->_objectManager->create(
                'Webkul\Marketplace\Helper\Email'
            )->sendProductStatusMail(
                $emailTemplateVariables,
                $senderInfo,
                $receiverInfo
            );

            $this->_eventManager->dispatch(
                'mp_approve_product',
                ['product' => $pro, 'seller' => $seller]
            );
        }
        $this->messageManager->addSuccess(
            __(
                'A total of %1 record(s) have been approved.',
                $collection->getSize()
            )
        );
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Marketplace::product');
    }
}
