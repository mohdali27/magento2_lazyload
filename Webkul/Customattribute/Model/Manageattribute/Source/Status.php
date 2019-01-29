<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Customattribute
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Customattribute\Model\Manageattribute\Source;

class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Webkul\Customattribute\Model\Manageattribute
     */
    protected $attributes;

    /**
     * Constructor
     *
     * @param \Webkul\Customattribute\Model\Manageattribute $post
     */
    public function __construct(\Webkul\Customattribute\Model\Manageattribute $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->attributes->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
