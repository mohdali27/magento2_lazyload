<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\Request;


/**
 * Class SaveDelivery
 * @package Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\Request
 */
class EditActivityInline extends \Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\AbstractRequest
{

    /**
     *
     */
    const TYPE_DELIVERY = 'delivery';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\Locator\LocatorFactory
     */
    protected $locatorFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\TransferStock\TransferActivityFactory
     */
    protected $transferActivityFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\TransferStock\TransferActivityProductFactory
     */
    protected $transferActivityProductFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    protected $adminSession;


    /**
     * SaveDelivery constructor.
     * @param \Magestore\InventorySuccess\Controller\Adminhtml\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Magestore\InventorySuccess\Model\Locator\LocatorFactory $locatorFactory
     * @param \Magestore\InventorySuccess\Model\TransferStock\TransferActivityFactory $transferActivityFactory
     * @param \Magestore\InventorySuccess\Model\TransferStock\TransferActivityProductFactory $transferActivityProductFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     */
    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magestore\InventorySuccess\Model\Locator\LocatorFactory $locatorFactory,
        \Magestore\InventorySuccess\Model\TransferStock\TransferActivityProductFactory $transferActivityProductFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Backend\Model\Auth\Session $adminSession
    ) {
        parent::__construct($context);
        $this->locatorFactory = $locatorFactory;
        $this->jsonFactory = $jsonFactory;
        $this->transferActivityFactory = $context->getTransferActivityFactory();
        $this->transferActivityProductFactory = $transferActivityProductFactory;
        $this->productFactory = $productFactory;
        $this->timezone = $timezone;
        $this->adminSession = $adminSession;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();


        $error = false;
        $messages = [];

        $adminUser = $this->adminSession->getUser();
        if ($adminUser->getId()) {
            $adminName = $adminUser->getUserName();
        } else {
            $adminName = '';
        }

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);

            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach ($postItems as $item){
                    $activityId = $item['activity_id'];
                    $note = $item['note'];
                    $transferActivityModel = $this->transferActivityFactory->create()->load($activityId);
                    if($transferActivityModel->getActivityId()){
                        $transferActivityModel->setNote($note);
                        //save transfer stock information
                        try {
                            $transferActivityModel->save();

                        } catch (\Magento\Framework\Exception\LocalizedException $e) {
                            $messages[] = $e->getMessage();
                        } catch (\Exception $e) {
                            $messages[] = $e->getMessage();
                        }
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * @param $productId
     * @param $adjustQty
     * @param $activityId
     */
    public function saveActivityProduct($productId, $adjustQty, $activityId) {
        $data = array();
        $data['activity_id'] = $activityId;
        $data['product_id'] = $productId;
        $data['qty'] = $adjustQty;
        $productModel = $this->productFactory->create()->load($productId);
        $data['product_name'] = $productModel->getData('name');
        $data['product_sku'] = $productModel->getData('sku');
        $this->transferActivityProductFactory->create()->setData($data)->save();
    }

}


