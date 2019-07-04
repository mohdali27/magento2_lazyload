<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

class Template extends \Amasty\Feed\Controller\Adminhtml\Feed\AbstractMassAction
{

    protected function massAction($collection)
    {
        foreach ($collection as $model) {
            $newModel = $this->feedCopier->template($model);
            $this->messageManager->addSuccessMessage(__('Template %1 was created', $model->getName()));
        }
    }
}
