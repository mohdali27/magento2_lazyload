<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model;

use Magento\Framework\Model\AbstractModel;

class Category extends AbstractModel
{
    const HREF = '<a target="_blank" href="https://support.google.com/merchants/answer/1705911?hl=en">Google Taxonomy</a>';

    /**
     * @var \Amasty\Feed\Model\ResourceModel\Category\Mapping
     */
    protected $_resourceMapping;

    /**
     * @var \Amasty\Feed\Model\Category\Mapping
     */
    protected $_mapping;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\Feed\Model\ResourceModel\Category $resource = null,
        \Amasty\Feed\Model\ResourceModel\Category\Collection $resourceCollection = null,
        \Amasty\Feed\Model\ResourceModel\Category\Mapping $resourceMapping,
        \Amasty\Feed\Model\Category\Mapping $mapping,

        array $data = []
    ) {
        $this->_resourceMapping = $resourceMapping;
        $this->_mapping = $mapping;

        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    protected function _construct()
    {
        $this->_init('Amasty\Feed\Model\ResourceModel\Category');
        $this->setIdFieldName('feed_category_id');
    }

    public function saveCategoryData()
    {
        $this->getResource()->save($this);
    }

    public function saveCategoriesMapping()
    {
        $this->_resourceMapping->saveCategoriesMapping($this, $this->getData("mapping"));
    }

    public function loadByCategoryId($categoryId)
    {
        $this->getResource()->loadByCategoryId($this, $categoryId);
        $this->_afterLoad();
        return $this;
    }

    protected function _afterLoad()
    {
        $collection = $this->_mapping->getCategoriesMappingCollection($this);
        if (!$this->getData('mapping')) {
            $mapping = [];
            foreach ($collection as $mappedCategory) {
                $mapping[$mappedCategory->getCategoryId()] = [
                    'name' => $mappedCategory->getVariable(),
                    'skip' => $mappedCategory->getSkip(),
                ];
            }
            $this->setData('mapping', $mapping);
        }

        parent::afterSave();
    }

    /**
     * @return ResourceModel\Category\Collection
     */
    public function getSortedCollection()
    {
        $collection = $this->getCollection();
        $collection->addOrder('name');

        return $collection;
    }

    /**
     * @return string
     */
    public function getMappingNote()
    {
        $hrefChange = ['::href' => self::HREF];

        $note = <<<HEREDOC
        Please check ::href and rename your categories to match the corresponding Google categories according
        to the requirements.<br/> 
        <b>Important!</b> You should define the full path of the category exactly as it is in the taxonomy.
        For instance, if you are trying to associate your Shorts category with Google's,
        you might rename it to "Apparel & Accessories > Clothing > Shorts".
HEREDOC;

        $resultNote = strtr($note, $hrefChange);

        return $resultNote;
    }

    /**
     * @return string
     */
    public function getExcludeNote()
    {
        return "Carefully review all the categories listed below and select those you want to exclude
        from your product feed by checking the corresponding checkbox(es). Excluded categories
        will not be mapped to Google Taxonomies and won't be included in the generated feed.";
    }
}
