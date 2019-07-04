<?php
namespace Mconnect\Customflatrate\Model\Config\Source;

class Flatrate implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('None')],
            ['value' => 'O', 'label' => __('Per Order')],
            ['value' => 'I', 'label' => __('Per Product')],
	    //['value' => 'P', 'label' => __('Per Product')],
        ];
    }
}
