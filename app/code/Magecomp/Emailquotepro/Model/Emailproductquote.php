<?php

namespace Magecomp\Emailquotepro\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\RequestException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Emailproductquote extends AbstractModel
{
    public function __construct(
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        $data = array()
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function _construct()
    {
        $this->_init('Magecomp\Emailquotepro\Model\ResourceModel\Emailproductquote');
    }
}