<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\Component\Form\Fieldset\Warehouse;

use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Fieldset;

/**
 * Class Websites Fieldset
 */
class StockMovement extends Fieldset
{
    /**
     * Store manager
     *
     * @var StoreManager
     */
    protected $storeManager;

    protected $_context;
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
        array $components = [],
        array $data = []
    ) {
        $this->_context = $context;
        parent::__construct($context, $components, $data);

    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        if(!$this->_context->getRequestParam('id', false))
            $this->_data['config']['visible'] = false;
            
    }
}
