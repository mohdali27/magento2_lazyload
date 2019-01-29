<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpCustom
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpCustom\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Tax\Model\Calculation\Rate;
/**
 * Webkul MpCustom WebkulCustomConfigObserver Observer.
 */
class WebkulCustomConfigObserver implements ObserverInterface
{
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Rate $taxRate
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->taxRate = $taxRate;
    }

    /**
     * WebkulCustomConfigObserver event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $collection = $this->taxRate->getCollection();
        $vatRate = $this->scopeConfig->getValue('marketplace/general_settings/vat_percent');

        //modify rate for tax rates of every country
        foreach ($collection as $rate) {
            if(strpos($rate->getCode(),'wk_custom') !== false) {
                $rate->setRate($vatRate);
                $rate->save();
            }
        }
    }
}
