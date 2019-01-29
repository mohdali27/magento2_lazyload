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

use Magento\Framework\View\Element\Template\Context;
use Webkul\Marketplace\Helper\Data as HelperData;
use Webkul\Marketplace\Helper\Dashboard\Data as HelperDashboard;
use Webkul\Marketplace\Model\ResourceModel\Feedback\CollectionFactory;

class ReviewChart extends \Magento\Framework\View\Element\Template
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
     * @var HelperData
     */
    protected $helper;

    /**
     * @var HelperDashboard
     */
    protected $helperDashboard;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context           $context
     * @param HelperData        $helper
     * @param HelperDashboard   $helperDashboard
     * @param CollectionFactory $collectionFactory
     * @param array             $data
     */
    public function __construct(
        Context $context,
        HelperData $helper,
        HelperDashboard $helperDashboard,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->helperDashboard = $helperDashboard;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get seller statistics graph image url.
     *
     * @return string
     */
    public function getSellerStatisticsGraphUrl($type = "feed_value")
    {
        $params = [
            'cht' => 'p',
        ];
        $feedbackCollection = $this->collectionFactory->create()
        ->addFieldToFilter(
            'seller_id',
            $this->helper->getCustomerId()
        );
        $allReviewCountArr = $feedbackCollection->getAllReviewCount($type);
        $allFiveStarReviewCount = $feedbackCollection->getAllReviewCount($type, 100);
        $allFourStarReviewCount = $feedbackCollection->getAllReviewCount($type, 80);
        $allThreeStarReviewCount = $feedbackCollection->getAllReviewCount($type, 60);
        $allTwoStarReviewCount = $feedbackCollection->getAllReviewCount($type, 40);
        $allOneStarReviewCount = $feedbackCollection->getAllReviewCount($type, 20);

        $allFiveStarReview = 0;
        $allFourStarReview = 0;
        $allThreeStarReview = 0;
        $allTwoStarReview = 0;
        $allOneStarReview = 0;
        if (!empty($allReviewCountArr[0])) {
            $allReviewCount = $allReviewCountArr[0];
            if (!empty($allFiveStarReviewCount[0])) {
                $allFiveStarReview = (100 * $allFiveStarReviewCount[0]) / $allReviewCount;
            }
            if (!empty($allFourStarReviewCount[0])) {
                $allFourStarReview = (100 * $allFourStarReviewCount[0]) / $allReviewCount;
            }
            if (!empty($allThreeStarReviewCount[0])) {
                $allThreeStarReview = (100 * $allThreeStarReviewCount[0]) / $allReviewCount;
            }
            if (!empty($allTwoStarReviewCount[0])) {
                $allTwoStarReview = (100 * $allTwoStarReviewCount[0]) / $allReviewCount;
            }
            if (!empty($allOneStarReviewCount[0])) {
                $allOneStarReview = (100 * $allOneStarReviewCount[0]) / $allReviewCount;
            }
        }

        $getReviewTitleArr = ["1 Star", "2 Star", "3 Star", "4 Star", "5 Star"];
        $getReviewPercentageArr = [
            round($allOneStarReview),
            round($allTwoStarReview),
            round($allThreeStarReview),
            round($allFourStarReview),
            round($allFiveStarReview)
        ];

        $params['chl'] = implode('%|', $getReviewPercentageArr);
        $chcoArr = ["f9d5c2", "f9bd9e", "fba477", "fd8140", "e04d00"];

        $params['chco'] = implode('|', $chcoArr);
        $params['chd'] = 't:'.implode(',', $getReviewPercentageArr); //"s:Uf9a";
        $params['chdl'] = implode('|', $getReviewTitleArr);
        $params['chl'] = $params['chl'].'%';

        $valueBuffer = [];

        // seller statistics graph size
        $params['chs'] = $this->_width.'x'.$this->_height;

        // return the encoded graph image url
        $getParamData = urlencode(base64_encode(json_encode($params)));
        $getEncryptedHashData = $this->helperDashboard
        ->getChartEncryptedHashData(
            $getParamData
        );
        $params = [
            'param_data' => $getParamData,
            'encrypted_data' => $getEncryptedHashData,
        ];

        return $this->getUrl(
            '*/*/dashboard_tunnel',
            [
                '_query' => $params,
                '_secure' => $this->getRequest()->isSecure()
            ]
        );
    }
}
