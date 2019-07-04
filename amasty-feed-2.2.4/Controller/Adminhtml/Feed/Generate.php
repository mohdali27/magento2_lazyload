<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

class Generate extends \Amasty\Feed\Controller\Adminhtml\Feed\AbstractMassAction
{
    protected function massAction($collection)
    {
        foreach ($collection as $model) {
            $page = 0;
            while (!$model->getExport()->getIsLastPage()) {
                $model->export(++$page);
            }
            $model->compress();
            $this->messageManager->addSuccessMessage(__('Feed %1 was generated', $model->getName()));
        }
    }
}
