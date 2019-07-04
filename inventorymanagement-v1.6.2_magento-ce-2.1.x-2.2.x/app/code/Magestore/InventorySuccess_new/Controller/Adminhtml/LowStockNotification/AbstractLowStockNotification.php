<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Controller\Adminhtml\LowStockNotification;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * class \Magestore\Webpos\Controller\Adminhtml\Location
 * 
 * Abstract location action class
 * Methods:
 *  _isAllowed
 * 
 * @category    Magestore
 * @package     Magestore\Webpos\Controller\Adminhtml\Location
 * @module      Webpos
 * @author      Magestore Developer
 */

use Magento\Framework\Registry;

abstract class AbstractLowStockNotification extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $_resultForwardFactory;
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $_resultLayoutFactory;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $_dateFilter;

    /**
    * @var DataPersistorInterface
    */
    protected $dataPersistor;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Rule\ProductFactory
     */
    protected $_ruleProductResourceFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        Registry $coreRegistry,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Rule\ProductFactory $ruleProductResourceFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\File\Csv $csvProcessor,
        TimezoneInterface $timezone
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->_resultForwardFactory = $resultForwardFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_dateFilter = $dateFilter;
        $this->dataPersistor = $dataPersistor;
        $this->_ruleProductResourceFactory = $ruleProductResourceFactory;
        $this->_fileFactory = $fileFactory;
        $this->timezone = $timezone;
        parent::__construct($context);
    }
}