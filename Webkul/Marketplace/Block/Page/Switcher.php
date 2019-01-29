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
namespace Webkul\Marketplace\Block\Page;

use Magento\Framework\Locale\Bundle\CurrencyBundle as CurrencyBundle;
use Magento\Directory\Helper\Data;
use Magento\Store\Model\Group;

class Switcher extends \Magento\Store\Block\Switcher
{
    /**
     * @var bool
     */
    protected $_storeInUrl;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_postDataHelper;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * Constructs
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = []
    ) {
        $this->_postDataHelper = $postDataHelper;
        $this->_currencyFactory = $currencyFactory;
        parent::__construct($context, $postDataHelper, $data);
        $this->localeResolver = $localeResolver;
    }

    /**
     * Retrieve count of currencies
     * Return 0 if only one currency
     *
     * @return int
     */
    public function getCurrencyCount()
    {
        $currencies = [];
        $codes = $this->_storeManager->getStore()->getAvailableCurrencyCodes(true);
        if (is_array($codes) && count($codes) > 1) {
            $rates = $this->_currencyFactory->create()->getCurrencyRates(
                $this->_storeManager->getStore()->getBaseCurrency(),
                $codes
            );

            foreach ($codes as $code) {
                if (isset($rates[$code])) {
                    $allCurrencies = (new CurrencyBundle())->get(
                        $this->localeResolver->getLocale()
                    )['Currencies'];
                    $currencies[$code] = $allCurrencies[$code][1] ?: $code;
                }
            }
        }
        return count($currencies);
    }
}
