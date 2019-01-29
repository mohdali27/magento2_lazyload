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
class ShippingMenu extends \Webkul\Marketplace\Block\Account\Navigation
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context,
     * @param \Magento\Framework\ObjectManagerInterface $objectManager,
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date,
     * @param \Magento\Customer\Model\Session $customerSession,
     * @param \Magento\Shipping\Model\Config $shipconfig
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Shipping\Model\Config $shipconfig
    ) {
        parent::__construct($context, $objectManager, $date, $customerSession);
        $this->shipconfig = $shipconfig;
    }

    /**
     * isShippineAvlForSeller
     * @return boolean
     */
    public function isShippineAvlForSeller()
    {
        $activeCarriers = $this->shipconfig->getActiveCarriers();
        $status = false;
        foreach ($activeCarriers as $carrierCode => $carrierModel) {
            $allowToSeller = $this->_scopeConfig->getValue(
                'carriers/'.$carrierCode.'/allow_seller'
            );
            if ($allowToSeller) {
                $status = true;
            }
        }
        return $status;
    }
}
