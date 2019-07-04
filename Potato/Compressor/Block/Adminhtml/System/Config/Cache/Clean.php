<?php
namespace Potato\Compressor\Block\Adminhtml\System\Config\Cache;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Clean extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'images_list',
                'label' => __('Flush Module Cache'),
                'class' => 'secondary',
                'onclick' => "setLocation('" . $this->getUrl('po_compressor/cache/clean') . "')",
            ]
        );
        return $button->toHtml();
    }
}