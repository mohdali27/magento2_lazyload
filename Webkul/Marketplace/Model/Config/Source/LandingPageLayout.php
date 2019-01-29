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

namespace Webkul\Marketplace\Model\Config\Source;

/**
 * Landing Page Layout options.
 */
class LandingPageLayout
{
    /**
     * Options getter.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $data = [
            ['value' => '1', 'label' => __('Layout 1')],
            ['value' => '2', 'label' => __('Layout 2')],
            ['value' => '3', 'label' => __('Layout 3')]
        ];
        return $data;
    }
}
