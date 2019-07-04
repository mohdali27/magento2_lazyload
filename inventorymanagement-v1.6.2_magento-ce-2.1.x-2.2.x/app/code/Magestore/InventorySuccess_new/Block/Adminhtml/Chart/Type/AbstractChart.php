<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\Chart\Type;

class AbstractChart extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Magestore_InventorySuccess::chart/type/column_chart.phtml';

    /**
     * @var string
     */
    protected $containerId;

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $subtitle = '';

    /**
     * @var string
     */
    protected $xAxisTitle = '';
    
    /**
     * @var string
     */
    protected $yAxisTitle = '';
    
    /**
     * @var string
     */
    protected $tooltip = '';

    /**
     * @var array
     */
    protected $seriesName = [];

    /**
     * @var array
     */
    protected $seriesType = [];

    /**
     * @var array
     */
    protected $seriesData = [];  
    
    /**
     * @var array
     */
    protected $seriesDataLabel = [];

    /**
     * @var array
     */
    protected $localeFormat;

    /**
     * AbstractChart constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Locale\Format $localeFormat
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Locale\Format $localeFormat,
        array $data = []
    ){
        $this->localeFormat = $localeFormat->getPriceFormat();
        parent::__construct($context, $data);
    }
    
    /**
     * Internal constructor, that is called from real constructor
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTitle('Abstract Chart');
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
     * set container id
     *
     * @param array $data
     * @return $this
     */
    public function setContainerId($containerId){
        $this->containerId = $containerId;
        return $this;
    }

    /**
     * get container id
     *
     * @return string
     */
    public function getContainerId(){
        return $this->containerId;
    }

    /**
     * set chart title
     * 
     * @param string $title
     * @return $this
     */
    public function setTitle($title = ''){
        $this->title = $title;
        return $this;
    }

    /**
     * get chart title
     * 
     * @return string
     */
    public function getTitle(){
        return $this->title;
    }

    /**
     * set chart subtitle
     * 
     * @param string $subtitle
     * @return $this
     */
    public function setSubtitle($subtitle = ''){
        $this->subtitle = $subtitle;
        return $this;
    }

    /**
     * get chart subtitle
     * 
     * @return string
     */
    public function getSubtitle(){
        return $this->subtitle;
    }

    /**
     * set X axis title
     *
     * @param string $title
     * @return $this
     */
    public function setXAxisTitle($title = ''){
        $this->xAxisTitle = $title;
        return $this;
    }

    /**
     * get X axis title
     * 
     * @return string
     */
    public function getXAxisTitle(){
        return $this->xAxisTitle;
    }

    /**
     * set Y axis title
     *
     * @param string $title
     * @return $this
     */
    public function setYAxisTitle($title = ''){
        $this->yAxisTitle = $title;
        return $this;
    }

    /**
     * get Y axis title
     *
     * @return string
     */
    public function getYAxisTitle(){
        return $this->yAxisTitle;
    }

    /**
     * set tooltip text
     *
     * @param string $title
     * @return $this
     */
    public function setTooltip($tooltip = ''){
        $this->tooltip = $tooltip;
        return $this;
    }

    /**
     * get tooltip text
     *
     * @return string
     */
    public function getTooltip(){
        return $this->tooltip;
    }

    /**
     * set series name
     * 
     * @param array $name
     * @return $this
     */
    public function setSeriesName($name = []){
        $this->seriesName = $name;
        return $this;
    }

    /**
     * get series name
     *
     * @return string
     */
    public function getSeriesName(){
        return $this->seriesName;
    }

    /**
     * set series type
     *
     * @param array $type
     * @return $this
     */
    public function setSeriesType($type = []){
        $this->seriesType = $type;
        return $this;
    }

    /**
     * get series type
     *
     * @return string
     */
    public function getSeriesType(){
        return $this->seriesType;
    }

    /**
     * set series data
     *
     * @param array $data
     * @return $this
     */
    public function setSeriesData($data = []){
        $this->seriesData = $data;
        return $this;
    }

    /**
     * get series data
     *
     * @return string
     */
    public function getSeriesData(){
        return $this->seriesData;
    }
    
    /**
     * set series data label
     *
     * @param array $data
     * @return $this
     */
    public function setSeriesDataLabel($dataLabel = []){
        $this->seriesDataLabel = $dataLabel;
        return $this;
    }

    /**
     * get series data label
     *
     * @return string
     */
    public function getSeriesDataLabel(){
        return $this->seriesDataLabel;
    }
}