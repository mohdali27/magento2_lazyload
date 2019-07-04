<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

class Edit extends \Amasty\Feed\Controller\Adminhtml\Feed
{
    /**
     * If Google Merchant Center rejects feed
     */
    const FAQ_LINK = 'https://amasty.com/knowledge-base/topic-product-feed.html#6976';

    /**
     * @var \Amasty\Feed\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * @var \Amasty\Feed\Model\ScheduleManagement
     */
    private $scheduleManagement;

    /**
     * @var \Magento\Framework\Message\Factory
     */
    private $messageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\Feed\Model\ScheduleManagement $scheduleManagement,
        \Amasty\Feed\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Message\Factory $messageFactory
    ) {
        $this->scheduleManagement = $scheduleManagement;
        $this->ruleFactory = $ruleFactory;
        $this->messageFactory = $messageFactory;

        parent::__construct($context, $coreRegistry, $resultLayoutFactory, $logger);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Amasty\Feed\Model\Feed');

        /** @var \Amasty\Feed\Model\Rule $rule */
        $rule = $this->ruleFactory->create();

        $message = __('Please keep in mind that your feed may not pass Google validation. Refer to '
            . '<a href=\'%1\' target=\'_blank\'>this article</a>'
            . ' and double check your feed.', self::FAQ_LINK);

        $this->messageManager->addMessage(
            $this->messageFactory->create(\Magento\Framework\Message\MessageInterface::TYPE_WARNING, $message)
        );

        if ($id) {
            $model->load($id);
            if (!$model->getEntityId()) {
                $this->messageManager->addErrorMessage(__('This feed no longer exists.'));
                $this->_redirect('amfeed/*');
                return;
            }
        }

        $rule->setConditions([]);
        $rule->setConditionsSerialized($model->getConditionsSerialized());

        $rule->getConditions()->setJsFormObject('rule_conditions_fieldset');

        // set entered data if was error when we do save
        $data = $this->_session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $model = $this->scheduleManagement->prepareScheduleData($model);

        $this->_coreRegistry->register('current_amfeed_feed', $model);
        $this->_coreRegistry->register('current_amfeed_rule', $rule);

        $this->_view->loadLayout();

        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getEntityId() ? $model->getName() : __('New Feed')
        );

        $this->_view->renderLayout();
    }
}
