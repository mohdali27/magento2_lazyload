<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Marketplace\Block\Adminhtml\Orders;

class Pay extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'seller/pay.phtml';

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $_formFactory;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;
    /**
     * Constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Customer\Model\Customer        $customer
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->_formFactory = $formFactory;
        $this->_customer = $customer;
        parent::__construct($context, $data);
    }

    public function getCustomer()
    {
        $sellerId = $this->getRequest()->getParam('seller_id');
        if ($sellerId) {
            return $this->_customer->load($sellerId);
        }
        return false;
    }
}
