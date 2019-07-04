<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

use Amasty\Feed\Api\Data\FeedInterface;
use Amasty\Feed\Model\Indexer\Feed\FeedRuleProcessor;
use Amasty\Feed\Model\Rule;
use Magento\Framework\Exception\LocalizedException;

class Save extends \Amasty\Feed\Controller\Adminhtml\Feed
{
    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    /**
     * @var \Amasty\Feed\Model\Indexer\Feed\FeedRuleProcessor
     */
    private $feedRuleProcessor;

    /**
     * @var \Amasty\Feed\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * @var \Amasty\Feed\Model\FeedRepository
     */
    private $feedRepository;

    /**
     * @var \Amasty\Feed\Model\ScheduleManagement
     */
    private $scheduleManagement;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\Feed\Model\RuleFactory $ruleFactory,
        \Amasty\Base\Model\Serializer $serializer,
        \Amasty\Feed\Model\ScheduleManagement $scheduleManagement,
        \Amasty\Feed\Model\FeedRepository $feedRepository,
        FeedRuleProcessor $feedRuleProcessor
    ) {
        parent::__construct($context, $coreRegistry, $resultLayoutFactory, $logger);

        $this->ruleFactory = $ruleFactory;
        $this->serializer = $serializer;
        $this->scheduleManagement = $scheduleManagement;
        $this->feedRepository = $feedRepository;
        $this->feedRuleProcessor = $feedRuleProcessor;
    }

    /**
     * @return FeedInterface
     *
     * @throws LocalizedException
     */
    protected function _save()
    {
        /** @var FeedInterface $model */
        $model = $this->feedRepository->getEmptyModel();

        if ($this->getRequest()->getPostValue()) {
            $data = $this->getRequest()->getPostValue();

            $id = $this->getRequest()->getParam('feed_entity_id');

            if ($id) {
                /** @var FeedInterface $model */
                $model = $this->feedRepository->getById($id);

                if ($id != $model->getEntityId()) {
                    throw new LocalizedException(__('The wrong feed is specified.'));
                }
            }

            if ($data['feed_type'] === 'xml') {
                if ((!isset($data['xml_header']) || !$data['xml_header'])
                    && (!isset($data['xml_footer']) || !$data['xml_footer'])
                ) {
                    $data['xml_header'] = '<?xml version="1.0"?>'
                        . '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"> <channel>'
                        . '<created_at>{{DATE}}</created_at>';
                    $data['xml_footer'] = '</channel> </rss>';
                }
            }

            if (isset($data['feed_entity_id'])) {
                $data['entity_id'] = $data['feed_entity_id'];
            }

            if (isset($data['store_ids'])) {
                $data['store_ids'] = implode(",", $data['store_ids']);
            }

            if (isset($data['csv_field'])) {
                $data['csv_field'] = $this->serializer->serialize($data['csv_field']);
            }

            if (isset($data['rule']) && isset($data['rule']['conditions'])) {
                $data['conditions'] = $data['rule']['conditions'];

                unset($data['rule']);

                /** @var Rule $rule */
                $rule = $this->ruleFactory->create();
                $rule->loadPost($data);

                $data['conditions_serialized'] = $this->serializer->serialize($rule->getConditions()->asArray());
                unset($data['conditions']);
            }

            if (isset($data['entity_id']) && $data['entity_id'] === "") {
                $data['entity_id'] = null;
            }

            $model->setData($data);

            $this->_session->setPageData($model->getData());

            $this->feedRepository->save($model);

            $this->scheduleManagement->saveScheduleData($model->getEntityId(), $data);

            if (!$this->feedRuleProcessor->getIndexer(FeedRuleProcessor::INDEXER_ID)->isScheduled()) {
                $this->feedRuleProcessor->reindexRow($model->getEntityId());
            }

            $this->_session->setPageData(false);
        }

        return $model;
    }

    public function execute()
    {
        try {
            $data = $this->getRequest()->getPostValue();

            $model = $this->_save();
            $this->messageManager->addSuccessMessage(__('You saved the feed.'));

            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('amfeed/feed/edit', ['id' => $model->getId()]);
                return;
            } elseif ($this->getRequest()->getParam('auto_apply')) {
                $this->_redirect('amfeed/feed/export', ['id' => $model->getId()]);
                return;
            }

            return $this->_redirect('amfeed/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $id = (int)$this->getRequest()->getParam('feed_entity_id');

            if (!empty($id)) {
                return $this->_redirect('amfeed/*/edit', ['id' => $id]);
            } else {
                return $this->_redirect('amfeed/*/new');
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving the feed data. Please review the error log.')
            );
            $this->logger->critical($e);
            $this->_session->setPageData($data);
            return $this->_redirect('amfeed/*/edit', ['id' => $this->getRequest()->getParam('feed_entity_id')]);
        }

        $this->_redirect('amfeed/*/');
    }
}
