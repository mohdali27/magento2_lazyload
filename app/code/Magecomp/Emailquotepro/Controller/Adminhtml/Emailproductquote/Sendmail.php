<?php

namespace Magecomp\Emailquotepro\Controller\Adminhtml\Emailproductquote;

use Magecomp\Emailquotepro\Controller\Adminhtml\Emailproductquote;
use Magento\Framework\Controller\ResultFactory;

class Sendmail extends Emailproductquote
{
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $this->initPage($resultPage);
        return $resultPage;
    }
}
