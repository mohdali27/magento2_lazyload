<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Created by PhpStorm.
 * User: Eden Duong
 * Date: 25/08/2016
 * Time: 9:09 SA
 */
namespace Magestore\InventorySuccess\Block\Adminhtml\Stocktaking\Steps;

use Magestore\InventorySuccess\Model\Stocktaking;

/**
 * Class Timeline
 * @package Magestore\InventorySuccess\Block\Adminhtml\Stocktaking\Steps
 */
class Timeline extends  \Magento\Ui\Block\Component\StepsWizard
{

    /**
     * Wizard step template
     *
     * @var string
     */
    protected $_template = 'Magestore_InventorySuccess::stocktaking/timeline.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Wizard step template
     *
     * @var string
     */
    public function getCurrentStep()
    {
        $step = 'variation-steps-wizard_new';
        $stocktaking = $this->coreRegistry->registry('current_stocktaking');
        if(isset($stocktaking) && $stocktaking->getId()) {
            $status = $stocktaking->getData('status');
            switch ($status) {
                case Stocktaking::STATUS_PROCESSING :
                    $step = 'variation-steps-wizard_processing';
                    break;
                case Stocktaking::STATUS_VERIFIED:
                    $step = 'variation-steps-wizard_verified';
                    break;
                case Stocktaking::STATUS_COMPLETED :
                    $step = 'variation-steps-wizard_completed';
                    break;
                case Stocktaking::STATUS_PENDING :
                    $step = 'variation-steps-wizard_pending';
                    break;
                default:
                    $step = 'variation-steps-wizard_new';
                    break;
            }
        }
        return $step;
    }
}