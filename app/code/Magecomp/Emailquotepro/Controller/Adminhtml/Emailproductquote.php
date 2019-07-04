<?php

namespace Magecomp\Emailquotepro\Controller\Adminhtml;

use Magecomp\Emailquotepro\Model\EmailproductquoteFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Registry;

abstract class Emailproductquote extends Action
{
    protected $emailproductquoteFactory;
    protected $registry;
    protected $context;

    public function __construct(
        EmailproductquoteFactory $EmailproductquoteFactory,
        Registry $registry,
        Context $context,
        ForwardFactory $resultForwardFactory
    )
    {
        $this->emailproductquoteFactory = $EmailproductquoteFactory;
        $this->registry = $registry;
        $this->context = $context;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    protected function initPage( $resultPage )
    {
        $resultPage->setActiveMenu('Magecomp_Emailquotepro::emailproductquote');
        $resultPage->getConfig()->getTitle()->prepend(__('Email Cart Statistics'));
        return $resultPage;
    }

    protected function initModel()
    {
        $model = $this->emailproductquoteFactory->create();
        if ($this->getRequest()->getParam('id')) {
            $model->load($this->getRequest()->getParam('id'));
        }
        $this->registry->register('current_model', $model);
        return $model;
    }

    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Magecomp_Emailquotepro::emailproductquote');
    }
}
