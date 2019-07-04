<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\GoogleWizard\Edit\Tab;

class General extends TabGeneric
{
    const HTML_ID_PREFIX = 'feed_googlewizard_general_';

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Amasty\Feed\Model\GoogleWizard
     */
    private $googleWizard;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    private $systemStore;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    private $currencyFactory;

    /**
     * @var \Amasty\Feed\Model\FeedFactory
     */
    private $feedFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Amasty\Feed\Model\RegistryContainer $registryContainer,
        \Amasty\Feed\Model\GoogleWizard $googleWizard,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Amasty\Feed\Model\FeedFactory $feedFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $registryContainer, $data);
        $this->layoutFactory = $layoutFactory;
        $this->googleWizard = $googleWizard;
        $this->systemStore = $systemStore;
        $this->currencyFactory = $currencyFactory;
        $this->feedFactory = $feedFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Step 1: General Settings');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Step 1: General Settings');
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareNotEmptyForm()
    {
        $feedId = $this->_request->getParam('amfeed_id');
        /** @var \Amasty\Feed\Model\Feed $model */
        $model = $this->feedFactory->create();

        if ($feedId) {
            $model->loadByFeedId($feedId);
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('feed_');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix(self::HTML_ID_PREFIX);

        $fieldset = $form->addFieldset('general_fieldset', ['legend' => $this->getLegend()]);

        if ($model->getId()) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'feed_entity_id']);
        } else {
            $model->setData('is_active', 1);

            $model->setData('csv_column_name', 1);

            $model->setData('format_price_currency_show', 1);
            $model->setData('format_price_decimals', 'two');
            $model->setData('format_price_decimal_point', 'dot');
            $model->setData('format_price_thousands_separator', 'comma');

            $model->setData('format_date', 'Y-m-d');
        }

        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Feed Name'),
                'title' => __('Feed Name'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'filename',
            'text',
            [
                'name' => 'filename',
                'label' => __('File Name'),
                'title' => __('File Name'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'is_active',
                'required' => true,
                'options' => [
                    '1' => __('Active'),
                    '0' => __('Inactive')
                ]
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField(
                'store_id',
                'select',
                [
                    'label' => __('Store View'),
                    'class' => 'required-entry',
                    'required' => true,
                    'name' => 'store_id',
                    'value' => $this->googleWizard->getStoreId(),
                    'values' => $this->systemStore->getStoreValuesForForm()
                ]
            );
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                [
                    'value' => $this->googleWizard->getStoreId()
                ]
            );
        }

        $fieldset->addField(
            'format_price_currency',
            'select',
            [
                'label' => __('Price Currency'),
                'name'  => 'format_price_currency',
                'value' => $this->googleWizard->getCurrency(),
                'options' => $this->getCurrencyList(),
            ]
        );
        $fieldset->addField(
            'exclude_disabled',
            'select',
            [
                'label' => __('Exclude Disabled Products'),
                'title' => __('Exclude Disabled Products'),
                'name' => 'exclude_disabled',
                'options' => [
                    '1' => __('Yes'),
                    '0' => __('No')
                ]
            ]
        );
        $fieldset->addField(
            'exclude_out_of_stock',
            'select',
            [
                'label' => __('Exclude Out of Stock Products'),
                'title' => __('Exclude Out of Stock Products'),
                'name' => 'exclude_out_of_stock',
                'options' => [
                    '1' => __('Yes'),
                    '0' => __('No')
                ]
            ]
        );
        $fieldset->addField(
            'exclude_not_visible',
            'select',
            [
                'label' => __('Exclude Not Visible Products'),
                'title' => __('Exclude Not Visible Products'),
                'name' => 'exclude_not_visible',
                'options' => [
                    '1' => __('Yes'),
                    '0' => __('No')
                ]
            ]
        );

        $form->setValues($model->getData());

        $this->setForm($form);

        return $this;
    }

    /**
     * Get currencies
     *
     * @return array
     */
    protected function getCurrencyList()
    {
        $instantCurrencyFactory = $this->currencyFactory->create();
        $currencies = $instantCurrencyFactory->getConfigAllowCurrencies();

        rsort($currencies);
        $retCurrencies = array_combine($currencies, $currencies);

        return $retCurrencies;
    }
}
