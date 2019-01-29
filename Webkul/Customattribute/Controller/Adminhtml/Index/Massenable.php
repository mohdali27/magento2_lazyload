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
namespace Webkul\Customattribute\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Customattribute\Model\ResourceModel\Systemattribute\CollectionFactory;

/**
 * Class massApprove.
 */
class Massenable extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;
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

    protected $_status = 1;

    /**
     * @param Context                                     $context
     * @param Filter                                      $filter
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime          $dateTime
     * @param CollectionFactory                           $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        CollectionFactory $collectionFactory
    ) {
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
        $this->_date = $date;
        $this->_storeManager = $storeManager;
        $this->_productRepository = $productRepository;
        $this->_dateTime = $dateTime;
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
        $selected = $this->getRequest()->getParam('selected');
        $excluded = $this->getRequest()->getParam('excluded');
        $ids = [];
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        foreach ($collection as $item) {
            $ids[] = $item->getId();
        }
        if ($excluded == 'false') {
            $excluded = [];
        }
        if (!empty($selected)) {
            $ids = array_intersect($ids, $selected);
            ;
        } elseif (!empty($excluded)) {
            $ids = array_diff($ids, $excluded);
        }
        foreach ($ids as $id) {
            $collection = $this->_objectManager
                ->create('Webkul\Customattribute\Model\Manageattribute')
                ->getCollection()->addFieldToFilter('attribute_id', $id);
            if (count($collection) != 0) {
                foreach ($collection as $row) {
                    $row->setStatus($this->_status);
                    $row->save();
                }
            } else {
                $querydata1 = $this->_objectManager->create('Webkul\Customattribute\Model\Manageattribute');
                $querydata1->setAttributeId($id);
                $querydata1->setStatus($this->_status);
                $querydata1->save();
            }
        }
        if ($this->_status) {
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been enabled.', count($ids)));
        } else {
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been disabled.', count($ids)));
        }

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
        return $this->_authorization->isAllowed('Webkul_Customattribute::customattribute');
    }
}
