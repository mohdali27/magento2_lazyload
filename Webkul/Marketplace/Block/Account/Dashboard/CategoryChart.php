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

namespace Webkul\Marketplace\Block\Account\Dashboard;

class CategoryChart extends \Magento\Framework\View\Element\Template
{
    /**
     * Google Api URL.
     */
    const GOOGLE_API_URL = 'http://chart.apis.google.com/chart';

    /**
     * Seller statistics graph width.
     *
     * @var string
     */
    protected $_width = '350';

    /**
     * Seller statistics graph height.
     *
     * @var string
     */
    protected $_height = '169';

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @param Context                                   $context
     * @param array                                     $data
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session           $customerSession
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    /**
     * Get seller statistics graph image url.
     *
     * @return string
     */
    public function getSellerStatisticsGraphUrl()
    {
        $params = [
            'cht' => 'p',
        ];
        $getTopSaleCategories = $this->_objectManager->get(
            'Webkul\Marketplace\Block\Account\Dashboard'
        )->getTopSaleCategories();
        $params['chl'] = implode('|', $getTopSaleCategories['category_arr']);
        $chcoArr = [];
        for ($i = 1; $i <= count($getTopSaleCategories['category_arr']); ++$i) {
            array_push($chcoArr, $this->randString());
        }

        $params['chco'] = implode('|', $chcoArr);
        $params['chd'] = 't:'.implode(',', $getTopSaleCategories['percentage_arr']); //"s:Uf9a";
        $params['chdl'] = implode('%|', $getTopSaleCategories['percentage_arr']);
        $params['chdl'] = $params['chdl'].'%';

        $valueBuffer = [];

        // seller statistics graph size
        $params['chs'] = $this->_width.'x'.$this->_height;

        // return the encoded graph image url
        $_sellerDashboardHelperData = $this->_objectManager->get(
            'Webkul\Marketplace\Helper\Dashboard\Data'
        );
        $getParamData = urlencode(base64_encode(json_encode($params)));
        $getEncryptedHashData =
        $_sellerDashboardHelperData->getChartEncryptedHashData($getParamData);
        $params = [
            'param_data' => $getParamData,
            'encrypted_data' => $getEncryptedHashData,
        ];

        return $this->getUrl(
            '*/*/dashboard_tunnel',
            ['_query' => $params, '_secure' => $this->getRequest()->isSecure()]
        );
    }

    public function randString(
        $charset = 'ABC0123456789'
    ) {
        $length = 6;
        $str = '';
        $count = strlen($charset);
        while ($length--) {
            $str .= $charset[mt_rand(0, $count - 1)];
        }

        return $str;
    }
}
