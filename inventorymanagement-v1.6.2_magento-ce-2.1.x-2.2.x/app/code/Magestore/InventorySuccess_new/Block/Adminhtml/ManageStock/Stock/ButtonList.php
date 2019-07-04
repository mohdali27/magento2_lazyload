<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\ManageStock\Stock;

class ButtonList extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Initialize Stock Management Page
     *
     * @return void
     */
    protected function _construct()
    {

        $this->_controller = 'adminhtml_manageStock';
        $this->_blockGroup = 'Magestore_InventorySuccess';
        parent::_construct();
        $this->buttonList->remove('reset');
        $this->buttonList->remove('back');

        $this->removeButton('save')
            ->addButton(
                'adjust-stock',
                [
                    'label' => __('Adjust Stock'),
                    'class' => 'save primary',
                    'onclick' => "",
                ], 0, 30
            )->addButton(
                'transfer-stock',
                [
                    'label' => __('Transfer Stock'),
                    'class' => 'save primary',
                    'onclick' => "",
                ], 0, 20
            )->addButton(
                'stock-taking',
                [
                    'label' => __('Physical Stock-taking'),
                    'class' => 'save primary',
                    'onclick' => "",
                ], 0, 10
            );

        $this->_eventManager->dispatch(
            'create_manage_stock_button_list',
            ['object' => $this]
        );
    }

    /**
     * Get header text for Stock Management page.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Manage Stock');
    }
}
