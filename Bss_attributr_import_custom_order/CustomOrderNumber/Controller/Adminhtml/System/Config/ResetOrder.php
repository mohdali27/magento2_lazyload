<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomOrderNumber
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomOrderNumber\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Bss\CustomOrderNumber\Model\ResourceModel\Sequence;

class ResetOrder extends Action
{
    /**
     * JsonFactory
     *
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Sequence
     *
     * @var Sequence
     */
    protected $sequence;

    /**
     * Construct
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Sequence $sequence
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Sequence $sequence
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sequence = $sequence;
        parent::__construct($context);
    }

    /**
     * Truncate Table
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $entityType = 'order';
        $storeId = $this->getRequest()->getParam('storeId');
        if ($storeId == 1) {
            $storeId = 0;
        }
        $this->sequence->resetSequence($entityType, $storeId);
        /* @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();
        
        return $result->setData(['success' => true, 'error' => false]);
    }

    /**
     * Allowed
     *
     * @return string
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_CustomOrderNumber::resetorder');
    }
}
