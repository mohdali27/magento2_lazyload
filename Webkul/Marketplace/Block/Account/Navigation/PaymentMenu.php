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
namespace Webkul\Marketplace\Block\Account\Navigation;

/**
 * Marketplace Navigation link
 *
 */
class PaymentMenu extends \Webkul\Marketplace\Block\Account\Navigation
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context,
     * @param \Magento\Framework\ObjectManagerInterface $objectManager,
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date,
     * @param \Magento\Customer\Model\Session $customerSession,
     * @param \Magento\Payment\Model\Config $paymentConfig
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Payment\Model\Config $paymentConfig
    ) {
        parent::__construct($context, $objectManager, $date, $customerSession);
        $this->paymentConfig = $paymentConfig;
    }

    /**
     * isPaymentAvlForSeller
     * @return boolean
     */
    public function isPaymentAvlForSeller()
    {
        $activeMethods = $this->paymentConfig->getActiveMethods();
        $status = false;
        foreach ($activeMethods as $methodCode => $methodModel) {
            if (preg_match('/mp[^a-b][^0-9][^A-Z]/', $methodCode)) {
                $status = true;
            }
        }
        return $status;
    }
}
