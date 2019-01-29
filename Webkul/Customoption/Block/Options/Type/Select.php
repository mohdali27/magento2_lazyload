<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Customoption
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Customoption\Block\Options\Type;

class Select extends \Webkul\Customoption\Block\Options\Type\AbstractType
{
    /**
     * @var string
     */
    protected $_template = 'catalog/product/edit/options/type/select.phtml';

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setCanEditPrice(true);
        $this->setCanReadPrice(true);
        $this->setTemplate('Webkul_Customoption::options/type/select.phtml');
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'delete_select_row_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Delete Row'),
                'class' => 'delete delete-select-row icon-btn',
                'id' => 'product_option_<%- data.id %>_select_<%- data.select_id %>_delete'
            ]
        );
        $this->addChild(
            'add_select_row_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Add New Row'),
                'class' => 'add add-select-row',
                'id' => 'product_option_<%- data.option_id %>_add_select_row'
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_select_row_button');
    }

    /**
     * @return string
     */
    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_select_row_button');
    }

    /**
     * Return select input for price type
     *
     * @param string $extraParams
     * @return string
     */
    public function getPriceTypeSelectHtml($extraParams = '')
    {
        $type = '';
        if ($this->getRequest()->getParam('type') &&
            ($this->getRequest()->getParam('type') == 'configurable' || $this->getRequest()->getParam('type') == 'bundle')) {
            $type = $this->getRequest()->getParam('type');
        } elseif ($this->getRequest()->getParam('id')) {
            if ($this->getParentBlock()->getProduct()->getTypeId() == 'configurable' || $this->getParentBlock()->getProduct()->getTypeId() == 'bundle') {
                $type = $this->getParentBlock()->getProduct()->getTypeId();
            }
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_optionPrice = $objectManager->get('Webkul\Customoption\Model\Config\Product\Price');
        $this->getChildBlock(
            'option_price_type'
        )->setData(
            'id',
            'product_option_<%- data.id %>_select_<%- data.select_id %>_price_type'
        )->setName(
            'product[options][<%- data.id %>][values][<%- data.select_id %>][price_type]'
        )->setOptions(
            $this->_optionPrice->toOptionArray($type)
        );

        return parent::getPriceTypeSelectHtml($extraParams);
    }
}
