<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Ui\Component\Listing\Columns\Frontend;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Customer\Api\CustomerMetadataInterface as CustomerMetadata;

/**
 * Class CustomerAttribute.
 */
class CustomerAttribute extends \Magento\Ui\Component\Listing\Columns
{
    /**
     * @var int
     */
    protected $sortOrder;

    /**
     * @var \Magento\Customer\Ui\Component\Listing\AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Customer\Ui\Component\Listing\Column\InlineEditUpdater
     */
    protected $inlineEditUpdater;

    /**
     * @var array
     */
    protected $typeFilterMap = [
        'default' => 'text',
        'select' => 'select',
        'boolean' => 'select',
        'multiselect' => 'select',
        'date' => 'dateRange',
    ];

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Customer\Ui\Component\ColumnFactory $columnFactory
     * @param \Magento\Customer\Ui\Component\Listing\AttributeRepository $attributeRepository
     * @param \Magento\Customer\Ui\Component\Listing\Column\InlineEditUpdater $inlineEditor
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Customer\Ui\Component\ColumnFactory $columnFactory,
        \Magento\Customer\Ui\Component\Listing\AttributeRepository $attributeRepository,
        \Magento\Customer\Ui\Component\Listing\Column\InlineEditUpdater $inlineEditor,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->columnFactory = $columnFactory;
        $this->attributeRepository = $attributeRepository;
        $this->inlineEditUpdater = $inlineEditor;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $this->sortOrder = $this->getDefaultSortOrder();
        $attributeList = $this->attributeRepository->getList();
        foreach ($attributeList as $attributeCode => $attributeData) {
            if (isset($this->components[$attributeCode])) {
                $this->updateCustomerColumn(
                    $attributeData,
                    $attributeCode
                );
            }
        }
        $this->updateActionSortOrder();
        parent::prepare();
    }

    /**
     * @param array $attributeData
     * @param string $attributeCode
     * @return void
     */
    public function updateCustomerColumn(array $attributeData, $attributeCode)
    {
        $component = $this->components[$attributeData[AttributeMetadataInterface::ATTRIBUTE_CODE]];
        $this->addCustomerAttrOptions($component, $attributeData);

        if ($attributeData[AttributeMetadataInterface::BACKEND_TYPE] != 'static') {
            if ($attributeData[AttributeMetadataInterface::IS_USED_IN_GRID]) {
                $config = array_merge(
                    $component->getData('config'),
                    [
                        'name' => $attributeCode,
                        'dataType' => $attributeData[AttributeMetadataInterface::BACKEND_TYPE],
                        'visible' => (bool)$attributeData[AttributeMetadataInterface::IS_VISIBLE_IN_GRID]
                    ]
                );
                if ($attributeData[AttributeMetadataInterface::IS_FILTERABLE_IN_GRID]) {
                    $config['filter'] = $this->getFilterType($attributeData[AttributeMetadataInterface::FRONTEND_INPUT]);
                }
                $component->setData('config', $config);
            }
        } else {
            if ($attributeData['entity_type_code'] == CustomerMetadata::ENTITY_TYPE_CUSTOMER
                && !empty($component->getData('config')['editor'])
            ) {
                $this->inlineEditUpdater->applyEditing(
                    $component,
                    $attributeData[AttributeMetadataInterface::FRONTEND_INPUT],
                    $attributeData[AttributeMetadataInterface::VALIDATION_RULES],
                    $attributeData[AttributeMetadataInterface::REQUIRED]
                );
            }
            $component->setData(
                'config',
                array_merge(
                    $component->getData('config'),
                    ['visible' => (bool)$attributeData[AttributeMetadataInterface::IS_VISIBLE_IN_GRID]]
                )
            );
        }
    }

    /**
     * Add options to component
     *
     * @param UiComponentInterface $component
     * @param array $attributeData
     * @return void
     */
    public function addCustomerAttrOptions(UiComponentInterface $component, array $attributeData)
    {
        $config = $component->getData('config');
        $attrOptions = AttributeMetadataInterface::OPTIONS;
        if (count($attributeData[$attrOptions]) && !isset($config[$attrOptions])) {
            $component->setData(
                'config',
                array_merge(
                    $config,
                    [$attrOptions => $attributeData[$attrOptions]]
                )
            );
        }
    }

    /**
     * Retrieve filter type by $frontendInput
     *
     * @param string $frontendInput
     * @return string
     */
    protected function getFilterType($frontendInput)
    {
        return isset($this->typeFilterMap[$frontendInput]) ? $this->typeFilterMap[$frontendInput] : $this->typeFilterMap['default'];
    }

    /**
     * @return int
     */
    protected function getDefaultSortOrder()
    {
        $maxCount = 0;
        foreach ($this->components as $component) {
            $config = $component->getData('config');
            if (isset($config['sortOrder']) && $config['sortOrder'] > $maxCount) {
                $maxCount = $config['sortOrder'];
            }
        }
        return ++$maxCount;
    }

    /**
     * Update actions column sort order
     *
     * @return void
     */
    protected function updateActionSortOrder()
    {
        if (isset($this->components['actions'])) {
            $component = $this->components['actions'];
            $component->setData(
                'config',
                array_merge(
                    $component->getData('config'), 
                    ['sortOrder' => ++$this->sortOrder]
                )
            );
        }
    }
}
