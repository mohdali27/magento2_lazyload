<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\Category\Edit\Tab;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class RenameMapping extends \Magento\Catalog\Block\Adminhtml\Category\AbstractCategory implements RendererInterface
{
    protected $_template = 'category/rename_mapping.phtml';

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    public function getCategoriesList($node = null)
    {
        $list = [];
        $root = $this->getRoot(null, 10);
        if ($root->hasChildren()) {
            foreach ($root->getChildren() as $node) {
                $this->_getChildCategories($list, $node);
            }
        }

        return $list;
    }

    protected function _getChildCategories(&$list, $node, $level = 0)
    {
        $list[] = [
            'name'  => $node->getName(),
            'id'    => $node->getId(),
            'level' => $level
        ];

        if ($node->hasChildren()) {
            foreach ($node->getChildren() as $child) {
                $this->_getChildCategories($list, $child, $level + 1);
            }
        }
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('amfeed/category/search');
    }
}
