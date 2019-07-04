<?php

namespace Magecomp\Emailquotepro\Observer;

use Magecomp\Emailquotepro\Helper\Data as EmailHelper;
use Magecomp\Emailquotepro\Model\EmailproductquoteFactory;
use Magecomp\Emailquotepro\Model\ResourceModel\Emailproductquote\Collection as EmailquoteCollecion;
use Magento\Framework\Event\ObserverInterface;

class Orderplaceafter implements ObserverInterface
{
    protected $objectManager;
    protected $_EmailproductquoteFactory;
    protected $_emailproductquoteCollection;
    protected $emailHelper;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        EmailquoteCollecion $emailproductquoteCollection,
        EmailproductquoteFactory $EmailproductquoteFactory,
        EmailHelper $emailHelper )
    {
        $this->_emailproductquoteCollection = $emailproductquoteCollection;
        $this->_EmailproductquoteFactory = $EmailproductquoteFactory;
        $this->objectManager = $objectManager;
        $this->emailHelper = $emailHelper;
    }

    public function execute( \Magento\Framework\Event\Observer $observer )
    {
        if ($this->emailHelper->isActive()) {
            $order_id = $observer->getData('order_ids');
            $order = $this->objectManager->create('Magento\Sales\Model\Order')->load($order_id[0]);
            $modelEmailProduct = $this->_emailproductquoteCollection->addFieldToFilter('quote_id', $order->getQuoteId());
            if (count($modelEmailProduct) > 0) {
                foreach ($modelEmailProduct as $modelEmailProductobj) {
                    $emailquoteObj = $this->_EmailproductquoteFactory->create()->load($modelEmailProductobj->getEmailproductquoteId());
                    $emailquoteObj->setStatus(3);
                    $emailquoteObj->save();
                }
            }
        }
        return true;
    }

}