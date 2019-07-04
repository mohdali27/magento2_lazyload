<?php
namespace Magecomp\Emailquotepro\Controller\Adminhtml\Emailproductquote;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;

class Index extends Action
{
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magecomp_Emailquotepro::emailquotepro_template_ui');
        $resultPage->addBreadcrumb(__('Email Quote'), __('Email Quote Statistics'));
        $resultPage->addBreadcrumb(__('Email Quote'), __('Email Quote Statistics'));
        $resultPage->getConfig()->getTitle()->prepend(__('Email Quote Statistics'));
        return $resultPage;
    }
    protected function _isAllowed()
    {
        return true;
    }
}