<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\GoogleWizard\Edit\Tab;

use Amasty\Feed\Block\Adminhtml\Category\Edit\Tab\ExcludeMapping as TabMapping;
use Amasty\Feed\Block\Adminhtml\Category\Edit\Tab\General as CategoryGeneral;

class ExcludeCategories extends TabGeneric
{
    /**
     * @var \Amasty\Feed\Model\Category
     */
    private $categoryMapper;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Amasty\Feed\Ui\Component\Form\GoogleTaxonomyOptions
     */
    private $googleTaxonomyOptions;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Amasty\Feed\Model\Category $categoryMapper,
        \Amasty\Feed\Model\RegistryContainer $registryContainer,
        \Amasty\Feed\Ui\Component\Form\GoogleTaxonomyOptions $googleTaxonomyOptions,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $registryContainer, $data);
        $this->feldsetId = 'base_fieldset';
        $this->layoutFactory = $layoutFactory;
        $this->categoryMapper = $categoryMapper;
        $this->googleTaxonomyOptions = $googleTaxonomyOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Step 2: Exclude Categories');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Step 2: Exclude Categories');
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareNotEmptyForm()
    {
        list($categoryMappingId, $feedId) = $this->getFeedStateConfiguration();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix(CategoryGeneral::HTML_ID_PREFIX);

        $fieldset = $form->addFieldset($this->feldsetId, ['legend' => $this->getLegend()]);

        if ($categoryMappingId) {
            $this->categoryMapper->loadByCategoryId($categoryMappingId);
            $fieldset->addField(
                'feed_category_id',
                'hidden',
                [
                    'name' => 'feed_category_id',
                    'value' => $categoryMappingId
                ]
            );
        } else {
            $this->categoryMapper->setData('is_active', 1);
        }

        if ($feedId) {
            $fieldset->addField(
                'feed_id',
                'hidden',
                [
                    'name'  => 'feed_id',
                    'value' => $feedId,
                ]
            );
        }

        $fieldset->addField(
            'mapping_note',
            'note',
            [
                'name' => 'mapping_note',
                'text' => $this->categoryMapper->getExcludeNote()
            ]
        );

        $fieldset->addField(
            'mapping',
            'note',
            ['name' => 'mapping']
        );

        $className = TabMapping::class;
        $form->getElement(
            'mapping'
        )->setRenderer(
            $this->layoutFactory->create()->createBlock($className)
        );
        $form->addValues($this->categoryMapper->getData());
        $this->setForm($form);

        return $this;
    }
}
