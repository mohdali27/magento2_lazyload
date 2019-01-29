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
namespace Webkul\Marketplace\Model\Product\Source;

use Magento\Sales\Ui\Component\Listing\Column\Status\Options as StatusOptions ;

/**
 * Class OrderListStatus
 */
class OrderListStatus extends StatusOptions
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = parent::toOptionArray();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' =>  $value['label'],
                'row_label' =>  "<span class='wk-mp-grid-status wk-mp-grid-status-".$value['value']."'>".$value['label']."</span>",
                'value' => $value['value'],
            ];
        }
        return $options;
    }
}
