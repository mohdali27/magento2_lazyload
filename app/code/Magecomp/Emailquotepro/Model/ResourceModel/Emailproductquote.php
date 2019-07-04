<?php

namespace Magecomp\Emailquotepro\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Emailproductquote extends AbstractDb
{
    protected function _construct()
    {
        $this->_init("emailproductquote", "emailproductquote_id");
    }
}