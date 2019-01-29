<?php
namespace Webkul\Marketplace\Block\Collection;

use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Model\Layer\FilterList as LayerFilterList;
use Magento\Catalog\Model\Layer\AvailabilityFlagInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class Navigation extends \Magento\LayeredNavigation\Block\Navigation
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $_categoryRepository;

    /**
     * @param Context $context
     * @param LayerResolver $layerResolver
     * @param LayerFilterList $filterList
     * @param AvailabilityFlagInterface $visibilityFlag
     * @param Registry $categoryRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        LayerResolver $layerResolver,
        LayerFilterList $filterList,
        AvailabilityFlagInterface $visibilityFlag,
        Registry $registry,
        CategoryRepositoryInterface $categoryRepository,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->_categoryRepository = $categoryRepository;
        parent::__construct(
            $context,
            $layerResolver,
            $filterList,
            $visibilityFlag,
            $data
        );
    }

    /**
     * Apply layer
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $paramData = $this->getRequest()->getParams();
        if (isset($paramData['c']) || isset($paramData['cat'])) {
            try {
                if (isset($paramData['c'])) {
                    $catId = $paramData['c'];
                }
                if (isset($paramData['cat'])) {
                    $catId = $paramData['cat'];
                }
                $category = $this->_categoryRepository->get($catId);
            } catch (\Exception $e) {
                $category = null;
            }

            if ($category) {
                $this->registry->registry('current_category', $category);
                $this->getLayer()->setData('current_category', $category);
            }
        }
        return parent::_prepareLayout();
    }
}
