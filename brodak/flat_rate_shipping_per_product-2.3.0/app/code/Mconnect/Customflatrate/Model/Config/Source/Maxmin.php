<?php
namespace Mconnect\Customflatrate\Model\Config\Source;

class Maxmin implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'max', 'label' => __('Max')],
            ['value' => 'min', 'label' => __('Min')],
        ];
    }
}
