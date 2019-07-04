<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml;

abstract class GoogleWizard extends \Magento\Backend\App\Action
{
    /**
     * @var \Amasty\Feed\Model\RegistryContainer
     */
    protected $registryContainer;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Amasty\Feed\Model\RegistryContainer $registryContainer,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->registryContainer = $registryContainer;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->logger = $logger;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Feed::feed');
    }
}
