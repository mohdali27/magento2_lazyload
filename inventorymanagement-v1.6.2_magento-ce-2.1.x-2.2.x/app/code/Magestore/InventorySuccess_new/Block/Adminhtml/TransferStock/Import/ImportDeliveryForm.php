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
namespace Magestore\InventorySuccess\Block\Adminhtml\TransferStock\Import;

class ImportDeliveryForm extends \Magento\Backend\Block\Template
{
    protected $_template = 'Magestore_InventorySuccess::transferstock/import_delivery_form.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
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

    public function getCsvSampleLink()
    {
        $url = $this->getUrl('inventorysuccess/transferstock_request/downloadsample', array('id' => $this->getRequest()->getParam('id')));
        return $url;
    }

}