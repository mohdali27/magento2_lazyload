<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\Chart\Type;

class AbstractPieChart extends \Magestore\InventorySuccess\Block\Adminhtml\Chart\Type\AbstractChart
{
    /**
     * @var string
     */
    protected $_template = 'Magestore_InventorySuccess::chart/block/pie_chart.phtml';

    /**
     * Internal constructor, that is called from real constructor
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTitle('Pie Chart');
    }

    /**
     * Preparing global layout
     *
     * You can redefine this method in child classes for changing layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }

    /**
     * Prepare data for chart
     */
    public function prepareData(){

    }
}