<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\GoogleWizard\Edit\Tab;

use Amasty\Feed\Block\Adminhtml\GoogleWizard\Edit\Tab\Content\EmptyElement;
use Amasty\Feed\Model\RegistryContainer;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

abstract class TabGeneric extends Generic implements TabInterface
{
    protected $feldsetId = '';

    protected $legend = '';

    /**
     * @var \Amasty\Feed\Model\RegistryContainer
     */
    protected $registryContainer;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\Feed\Model\RegistryContainer $registryContainer,
        array $data = []
    ) {
        $this->registryContainer = $registryContainer;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public abstract function getTabLabel();

    /**
     * @return \Magento\Framework\Phrase
     */
    public abstract function getTabTitle();

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $this->prepareNotEmptyForm();

        return parent::_prepareForm();
    }

    /**
     * Get state of current feed configuration
     *
     * @return array;
     */
    protected function getFeedStateConfiguration()
    {
        $categoryMappingId = $this->registryContainer->getValue(RegistryContainer::VAR_CATEGORY_MAPPER);
        $feedId = $this->registryContainer->getValue(RegistryContainer::VAR_FEED);

        return [$categoryMappingId, $feedId];
    }

    /**
     * Get legend of fieldset
     *
     * @return string;
     */
    protected function getLegend()
    {
        return ($this->legend) ? $this->legend : $this->getTabTitle();
    }

    /**
     * Prepare not-empty form before rendering HTML
     *
     * @return $this;
     */
    protected abstract function prepareNotEmptyForm();
}
