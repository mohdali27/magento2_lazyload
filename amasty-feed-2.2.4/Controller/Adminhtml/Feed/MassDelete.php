<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

class MassDelete extends \Amasty\Feed\Controller\Adminhtml\Feed\AbstractMassAction
{

    protected function massAction($collection)
    {
        foreach ($collection as $model) {
            $model->delete();
            $this->messageManager->addSuccessMessage(__('Feed %1 was deleted', $model->getName()));
        }
    }
}
