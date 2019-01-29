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
namespace Webkul\Marketplace\Model\Notification\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Webkul\Marketplace\Model\Notification;
use Webkul\Marketplace\Helper\Data as HelperData;

/**
 * Class Type
 */
class Type implements OptionSourceInterface
{
    /**
     * @var Notification
     */
    protected $marketplaceNotification;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * Constructor
     *
     * @param Notification $marketplaceNotification
     * @param HelperData   $helperData
     */
    public function __construct(
        Notification $marketplaceNotification,
        HelperData $helperData
    ) {
        $this->marketplaceNotification = $marketplaceNotification;
        $this->helperData = $helperData;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $helper = $this->helperData;
        $allTypes = $this->marketplaceNotification->getAllTypes();
        $options = [];
        foreach ($allTypes as $key => $value) {
            if ($key == Notification::TYPE_REVIEW) {
                if ($helper->getSellerProfileDisplayFlag()) {
                    $options[] = [
                        'label' => $value,
                        'value' => $key,
                    ];
                }
            } else {
                $options[] = [
                    'label' => $value,
                    'value' => $key,
                ];
            }
        }
        return $options;
    }
}
