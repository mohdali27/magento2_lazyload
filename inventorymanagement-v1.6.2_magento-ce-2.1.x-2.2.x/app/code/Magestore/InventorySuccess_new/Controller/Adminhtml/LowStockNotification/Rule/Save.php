<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Controller\Adminhtml\LowStockNotification\Rule;

class Save extends \Magestore\InventorySuccess\Controller\Adminhtml\LowStockNotification\AbstractLowStockNotification
{
    protected $formatDate = [
        'd/MM/y' => 'd/m/Y',
        'dd/MM/yy' => 'd/m/Y',
        'dd/MM/y' => 'd/m/Y',
        'd/M/yy' => 'd/m/Y',
        'd/M/y' => 'd/m/Y',
        'dd/M/yy' => 'd/m/Y'
    ];
    
    const ADMIN_RESOURCE = 'Magestore_InventorySuccess::view_notification_rule';
    /**
     * Promo quote save action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            /** @var \Magestore\InventorySuccess\Model\LowStockNotification\Rule $model */
            $model = $this->_objectManager->create('Magestore\InventorySuccess\Model\LowStockNotification\Rule');

            try {
                $this->_eventManager->dispatch(
                    'adminhtml_controller_inventorysuccess_lowstocknotification_rule_prepare_save',
                    ['request' => $this->getRequest()]
                );
                $data = $this->getRequest()->getPostValue();
               // \Zend_Debug::dump($data);
                $id = $this->getRequest()->getParam('rule_id');
                if ($id) {
                    $model = $model->load($id);
                } else {
                    unset($data['rule_id']);
                }
                if (isset($data['rule'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                    unset($data['rule']);
                }
                if (isset($data['specific_time'])) {
                    $data['specific_time'] = implode(',', $data['specific_time']);
                }
                if (isset($data['specific_day']) && $data['specific_day'] != "") {
                    $data['specific_day'] = implode(',', $data['specific_day']);
                }
                if (isset($data['specific_month']) && $data['specific_month'] != "") {
                    $data['specific_month'] = implode(',', $data['specific_month']);
                }
                if (isset($data['warehouse_ids']) && count($data['warehouse_ids']) && is_array($data['warehouse_ids'])) {
                    $data['warehouse_ids'] = implode(',', $data['warehouse_ids']);
                }
                $dateFormat = $this->timezone->getDateFormat();
                if(isset($this->formatDate[$dateFormat]) && $date = \DateTime::createFromFormat($this->formatDate[$dateFormat], $data['from_date'])) {
                    $data['from_date'] = $date->format('Y-m-d');
                }
                if(isset($this->formatDate[$dateFormat]) && $date = \DateTime::createFromFormat($this->formatDate[$dateFormat], $data['to_date'])) {
                    $data['to_date'] = $date->format('Y-m-d');
                }
              
                $data['apply'] = \Magestore\InventorySuccess\Model\LowStockNotification\Rule::NOT_APPLY;
                $model->loadPost($data);

                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->dataPersistor->set('lowstock_notification_rule', $data);
                $model->save($model);
                $this->messageManager->addSuccess(__('You saved the rule.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData(false);
                $this->dataPersistor->clear('lowstock_notification_rule');

                if ($this->getRequest()->getParam('auto_apply')) {
                    if ($model->getStatus() == \Magestore\InventorySuccess\Model\LowStockNotification\Rule::STATUS_ACTIVE) {
                        $this->_ruleProductResourceFactory->create()->applyRule($model);
                    }
                    $this->_redirect('inventorysuccess/*/edit', ['id' => $model->getId()]);
                    return;
                } else {
                    if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('inventorysuccess/*/edit', ['id' => $model->getId()]);
                        return;
                    }
                    $this->_redirect('inventorysuccess/*/');
                    return;
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the rule data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->dataPersistor->set('catalog_rule', $data);
                $this->_redirect('inventorysuccess/*/edit', ['id' => $this->getRequest()->getParam('rule_id')]);
                return;
            }
        }
        $this->_redirect('inventorysuccess/*/');
    }
}
