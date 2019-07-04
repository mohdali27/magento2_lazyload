<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model;

use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Framework\View\Element\AbstractBlock;

class FormFieldDependency
{
    /**
     * @var FieldFactory
     */
    private $fieldFactory;

    /**
     * @var array
     */
    private $dependArray =[];

    public function __construct(FieldFactory $fieldFactory)
    {
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * @param string $whatId
     * @param string $fromId
     * @param array|string $value
     */
    public function addDepend($whatId, $fromId, $value)
    {
        $this->dependArray[] = [
            'what' => $whatId,
            'from' => $fromId,
            'value' => is_array($value) ? implode(",", $value) : $value
        ];
    }

    /**
     * @param AbstractBlock $block
     */
    public function depend(AbstractBlock $block)
    {
        /** @var Dependence $blockDependence */
        $blockDependence = $block->getLayout()->createBlock(
            Dependence::class
        );

        /** @var array $depend */
        foreach ($this->dependArray as $depend) {
            $refField = $this->fieldFactory->create(
                [
                    'fieldData' => [
                        'value' => $depend['value'],
                        'separator' => ','
                    ],
                    'fieldPrefix' => ''
                ]
            );

            $blockDependence->addFieldMap($depend['what'], $depend['what'])
                ->addFieldMap($depend['from'], $depend['from'])
                ->addFieldDependence(
                    $depend['what'],
                    $depend['from'],
                    $refField
                );
        }

        $block->setChild(
            'form_after',
            $blockDependence
        );
    }
}
