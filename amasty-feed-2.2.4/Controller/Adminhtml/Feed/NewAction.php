<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

class NewAction extends \Amasty\Feed\Controller\Adminhtml\Feed
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
