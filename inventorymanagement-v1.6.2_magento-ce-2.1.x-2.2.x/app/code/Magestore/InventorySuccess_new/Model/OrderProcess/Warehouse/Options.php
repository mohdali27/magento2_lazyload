<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\OrderProcess\Warehouse;

class Options implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magestore\InventorySuccess\Api\OrderProcess\OrderProcessServiceInterface
     */
    protected $orderProcessService;

    /**
     * @var null|array
     */
    protected $options;

    /**
     * @var bool
     */
    protected $showAnonymousRow = false;

    /**
     * Options constructor.
     * @param \Magestore\InventorySuccess\Api\OrderProcess\OrderProcessServiceInterface $orderProcessService
     */
    public function __construct(
        \Magestore\InventorySuccess\Api\OrderProcess\OrderProcessServiceInterface $orderProcessService
    )
    {
        $this->orderProcessService = $orderProcessService;
    }

    /**
     *
     * @return \Magestore\InventorySuccess\Model\Warehouse\Options
     */
    public function showDummyRow()
    {
        $this->showAnonymousRow = true;
        return $this;
    }

    /**
     * @return array|null
     */
    public function toOptionArray()
    {
        if (null == $this->options) {
            if ($this->showAnonymousRow) {
                $this->options = [['value' => 0, 'label' => __('Select Location')]];
            } else {
                $this->options = [];
            }
            $collection = $this->orderProcessService->getViewWarehouseList();
            $this->options = array_merge($this->options, $collection->toOptionArray());
        }

        return $this->options;
    }
}
