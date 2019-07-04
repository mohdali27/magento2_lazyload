<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\GoogleWizard;

use Amasty\Feed\Model\RegistryContainer;

class Index extends \Amasty\Feed\Controller\Adminhtml\GoogleWizard
{
    public function __construct(\Magento\Backend\App\Action\Context $context,
        \Amasty\Feed\Model\RegistryContainer $registryContainer,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct(
            $context, $registryContainer, $resultLayoutFactory, $logger
        );
    }

    /**
     * Catalog categories index action
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $valueOfFirstStep = RegistryContainer::VALUE_FIRST_STEP;
        $step = $this->getRequest()->getParam(RegistryContainer::VAR_STEP, $valueOfFirstStep);
        $categoryMappedId = $this->getRequest()->getParam(RegistryContainer::VAR_CATEGORY_MAPPER);
        $identifierExistsId = $this->getRequest()->getParam(RegistryContainer::VAR_IDENTIFIER_EXISTS);
        $feedId = $this->getRequest()->getParam(RegistryContainer::VAR_FEED);

        $this->registryContainer->setValue(RegistryContainer::VAR_CATEGORY_MAPPER, $categoryMappedId);
        $this->registryContainer->setValue(RegistryContainer::VAR_FEED, $feedId);
        $this->registryContainer->setValue(RegistryContainer::VAR_IDENTIFIER_EXISTS, $identifierExistsId);
        $this->registryContainer->setValue(RegistryContainer::VAR_STEP, $step);

        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Google Feed Wizard'));
        $this->_view->renderLayout();
    }
}
