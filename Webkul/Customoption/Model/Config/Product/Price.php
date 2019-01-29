<?php

namespace Webkul\Customoption\Model\Config\Product;

class Price implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray($type = '')
    {
        if ($type) {
            return [
                ['value' => 'fixed', 'label' => __('Fixed')]
            ];
        }
        return [
            ['value' => 'fixed', 'label' => __('Fixed')],
            ['value' => 'percent', 'label' => __('Percent')]
        ];
    }
}
