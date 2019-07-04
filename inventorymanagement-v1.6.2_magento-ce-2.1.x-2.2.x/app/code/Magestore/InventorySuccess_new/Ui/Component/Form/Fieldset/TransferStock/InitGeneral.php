<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\Component\Form\Fieldset\TransferStock;

use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magestore\InventorySuccess\Model\TransferStock;

/**
 * Class Websites Fieldset
 */
class InitGeneral extends Fieldset
{
    /**
     * Store manager
     *
     * @var StoreManager
     */
    protected $storeManager;


    private $_registry;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param StoreManager $storeManager
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        StoreManager $storeManager,
        \Magento\Framework\Registry $registry,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->storeManager = $storeManager;
        $this->_registry = $registry;

    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        /** @var  /** @var \Magestore\InventorySuccess\Model\TransferStock $tranferStock */
        $tranferStock = $this->_registry->registry("inventorysuccess_transferstock");

    }
}
