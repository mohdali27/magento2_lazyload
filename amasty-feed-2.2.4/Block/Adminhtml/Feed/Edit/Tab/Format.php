<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\Feed\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Format extends Generic implements TabInterface{
    protected $_systemStore;
    protected $_currencyFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_currencyFactory = $currencyFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getTabLabel()
    {
        return __('Format');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('Format');
    }

    /**
     * Returns status flag about this tab can be showed or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return Form
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_amfeed_feed');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('feed_');

        $fieldset = $form->addFieldset('price_fieldset', ['legend' => __('Price')]);

        $fieldset->addField(
            'format_price_currency',
            'select',
            [
                'name' => 'format_price_currency',
                'label' => __('Currency'),
                'title' => __('Currency'),
                'required' => true,
                'options' => $this->_getCurrencyList()
            ]
        );

        $fieldset->addField(
            'format_price_currency_show',
            'select',
            [
                'label' => __('Show Currency Abbr'),
                'title' => __('Show Currency Abbr'),
                'name' => 'format_price_currency_show',
                'required' => true,
                'options' => [
                    '1' => __('Yes'),
                    '0' => __('No')
                ]
            ]
        );

        $fieldset->addField(
            'format_price_decimals',
            'select',
            [
                'name' => 'format_price_decimals',
                'label' => __('Number of decimal points'),
                'title' => __('Number of decimal points'),
                'required' => true,
                'options' => [
                    'one' => __('One'),
                    'two' => __('Two'),
                    'three' => __('Three'),
                    'four' => __('Four'),
                ]
            ]
        );

        $fieldset->addField(
            'format_price_decimal_point',
            'select',
            [
                'name' => 'format_price_decimal_point',
                'label' => __('Separator for the decimal point'),
                'title' => __('Separator for the decimal point'),
                'required' => true,
                'options' => [
                    'comma' => __('Comma (,)'),
                    'dot' => __('Dot (.)'),
                ]
            ]
        );

        $fieldset->addField(
            'format_price_thousands_separator',
            'select',
            [
                'name' => 'format_price_thousands_separator',
                'label' => __('Thousands Separator'),
                'title' => __('Thousands Separator'),
                'required' => true,
                'options' => [
                    'comma' => __('Comma (,)'),
                    'dot' => __('Dot (.)'),
                    'space' => __('Space ( )'),
                ]
            ]
        );

        $fieldset = $form->addFieldset('date_fieldset', ['legend' => __('Date')]);

        $fieldset->addField(
            'format_date',
            'text',
            [
                'name' => 'format_date',
                'label' => __('Date'),
                'title' => __('Date')
            ]
        );

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _getCurrencyList()
    {
        $currencies = $this->_currencyFactory->create()->getConfigAllowCurrencies();

        rsort($currencies);

        $ret = [];

        foreach($currencies as $currency)
            $ret[$currency] = $currency;

        return $ret;
    }
}
