<?php
namespace Mconnect\Customflatrate\Model\Config\Source;

class Methodtype implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'o', 'label' => __('Per Order')],
            ['value' => 'p', 'label' => __('Per Product')],
        ];
    }
}
