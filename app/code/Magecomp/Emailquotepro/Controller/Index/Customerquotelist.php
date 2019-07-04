<?php

namespace Magecomp\Emailquotepro\Controller\Index;

class Customerquotelist extends \Magento\Framework\App\Action\Action
{
    protected $custsession;
    protected $_responseFactory;
    protected $_url;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $custsession,
        \Magento\Framework\App\ResponseFactory $responseFactory
    )
    {
        $this->custsession = $custsession;
        $this->_responseFactory = $responseFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->custsession->isLoggedIn()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        } else {
            $this->_view->loadLayout();
            $this->_view->renderLayout();
        }
    }

}