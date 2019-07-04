<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Ui\Component\Listing\Column;

class Mode implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $options;
    protected $_executeModeList;

    public function __construct(\Amasty\Feed\Model\ResourceModel\Feed\Grid\ExecuteModeList $executeModeList)
    {
        $this->_executeModeList = $executeModeList;
    }

    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [];
            foreach ($this->_executeModeList->toOptionArray() as $value => $label) {
                $this->options[] = [
                    'value' => $value,
                    'label' => $label
                ];
            }
        }

        return $this->options;
    }
}
