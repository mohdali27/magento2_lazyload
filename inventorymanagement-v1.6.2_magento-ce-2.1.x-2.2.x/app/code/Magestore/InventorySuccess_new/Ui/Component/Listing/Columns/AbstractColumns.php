<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */

namespace Magestore\InventorySuccess\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentInterface;

class AbstractColumns extends \Magento\Ui\Component\Listing\Columns
{

    /**
     * @var \Magestore\PurchaseOrderSuccess\Service\Config\ProductConfig
     */
    protected $helper;

    protected $columnsThumbnail = 'image';

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magestore\InventorySuccess\Helper\Data $helper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->helper = $helper;
    }

    public function prepare()
    {
        $ret = parent::prepare();

        $this->_prepareColumns();
        return $ret;
    }

    protected function _prepareColumns()
    {
        foreach ($this->components as $id => $column) {
            if ($column instanceof \Magento\Ui\Component\Listing\Columns\Column) {
                if(!$this->checkProductSource() && ($id == $this->columnsThumbnail)) {
                    unset($this->components[$id]);
                }
            }
        }
    }

    protected function checkProductSource() {
        return $this->helper->getShowThumbnailProduct();
    }
}
