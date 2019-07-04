<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\GoogleWizard\Edit;

use Amasty\Feed\Model\RegistryContainer;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     *
     * @var \Amasty\Feed\Model\RegistryContainer
     */
    private $registryContainer;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Amasty\Feed\Model\RegistryContainer $registryContainer,
        array $data = []
    ) {
        $this->registryContainer = $registryContainer;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('googlewizard_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Setup Google Feed'));
    }
}