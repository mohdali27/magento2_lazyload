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

namespace Webkul\Marketplace\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Paylink.
 */
class Paylink extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor.
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$fieldName.'_flag'] = 0;
                //$item[$fieldName . '_html'] = "<button class='button'><span>deny</span></button>";

                if (($item['paid_status'] == 0) && ($item['cpprostatus'] == 1)) {
                    $item[$fieldName.'_flag'] = 1;
                    $item[$fieldName.'_html'] = '<button type="button" class="button wk_payseller" auto-id="'.$item['entity_id'].'" title="'.__('Pay Seller').'"><span><span><span>'.__('Pay Seller').'</span></span></span></button>';
                } elseif (($item['paid_status'] == 0 || $item['paid_status'] == 4) && ($item['cpprostatus'] == 0)) {
                    $item[$fieldName.'_html'] = __('Item Pending');
                } elseif (($item['paid_status'] == 0 || $item['paid_status'] == 4 || $item['paid_status'] == 2) && ($item['cpprostatus'] == 1) && ($item['status'] != 'complete')) {
                    $item[$fieldName.'_html'] = __('Item Pending');
                } else {
                    if ($item['paid_status'] == 4) {
                        $item[$fieldName.'_html'] = __('Item Cancelled');
                    } elseif ($item['paid_status'] == 3) {
                        $item[$fieldName.'_html'] = __('Item Refunded');
                    } else {
                        $item[$fieldName.'_html'] = __('Already Paid');
                    }
                }
                $item[$fieldName.'_title'] = __('Add a Notify Message To Seller?');
                $item[$fieldName.'_submitlabel'] = __('Pay');
                $item[$fieldName.'_cancellabel'] = __('Reset');
                $item[$fieldName.'_sellerid'] = $item['seller_id'];
                $item[$fieldName.'_autoorderid'] = $item['entity_id'];

                $item[$fieldName.'_formaction'] = $this->urlBuilder->getUrl('marketplace/order/payseller');
            }
        }

        return $dataSource;
    }
}
