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

namespace Webkul\Marketplace\Block\Account;

use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Webkul Marketplace Account Editprofile Block
 */
class Editprofile extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_countryCollectionFactory;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param \Webkul\Marketplace\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        DataPersistorInterface $dataPersistor,
        \Webkul\Marketplace\Helper\Data $helper,
        array $data = []
    ) {
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->dataPersistor = $dataPersistor;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    public function getWysiwygConfig()
    {
        $config = $this->_wysiwygConfig->getConfig();
        $config = json_encode($config->getData());
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    /**
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    public function getCountryCollection()
    {
        $collection = $this->_countryCollectionFactory->create()->loadByStore();
        return $collection;
    }

    /**
     * Retrieve list of top destinations countries
     *
     * @return array
     */
    protected function getTopDestinations()
    {
        $destinations = (string)$this->_scopeConfig->getValue(
            'general/country/destinations',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return !empty($destinations) ? explode(',', $destinations) : [];
    }

    /**
     * Retrieve list of countries option array
     *
     * @return array
     */
    public function getCountryOptionArray()
    {
        return $options = $this->getCountryCollection()
                ->setForegroundCountries($this->getTopDestinations())
                ->toOptionArray();
    }

    public function getPersistentData()
    {
        $partner = $this->helper->getSeller();
        $persistentData = (array)$this->dataPersistor->get('seller_profile_data');
        foreach ($partner as $key => $value) {
            if (empty($persistentData[$key])) {
                $persistentData[$key] = $value;
            }
        }
        $this->dataPersistor->clear('seller_profile_data');
        return $persistentData;
    }
}
