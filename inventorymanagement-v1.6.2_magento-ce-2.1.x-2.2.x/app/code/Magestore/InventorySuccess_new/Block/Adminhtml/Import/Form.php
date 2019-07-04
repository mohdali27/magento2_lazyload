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
namespace Magestore\InventorySuccess\Block\Adminhtml\Import;
class Form extends  \Magento\Backend\Block\Widget\Form\Generic {

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->urlBuilder = $context->getUrlBuilder();
        $this->setUseContainer(true);
    }

    /**
     * Get html id
     *
     * @return mixed
     */
    public function getHtmlId()
    {
        if (null === $this->getData('id')) {
            $this->setData('id', $this->mathRandom->getUniqueHash('id_'));
        }
        return $this->getData('id');
    }

    /**
     * Get csv sample link
     *
     * @return mixed
     */
    public function getCsvSampleLink() {
        return;
    }

    /**
     * Get content
     *
     * @return mixed
     */
    public function getContent() {
        return;
    }

    /**
     * Get import urk
     *
     * @return mixed
     */
    public function getImportLink() {
        return;
    }

    /**
     * Get import title
     *
     * @return string
     */
    public function getTitle() {
        return;
    }

}