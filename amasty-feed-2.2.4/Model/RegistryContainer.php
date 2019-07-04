<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model;

class RegistryContainer
{
    const TYPE_ATTRIBUTE = 'attribute';
    const TYPE_CUSTOM_FIELD = 'custom_field';
    const TYPE_CATEGORY = 'category';
    const TYPE_IMAGE = 'image';
    const TYPE_TEXT = 'text';

    const VAR_STEP = 'amfeed_step';
    const VAR_CATEGORY_MAPPER = 'amfeed_category_mapper';
    const VAR_IDENTIFIER_EXISTS = 'amfeed_identifier_exists';
    const VAR_FEED = 'amfeed_id';

    const VALUE_FIRST_STEP = 1;
    const VALUE_LAST_STEP = 6;

    const MAX_ADDITIONAL_IMAGES = 5;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * Set value in core registry
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function setValue($key, $value)
    {
        $this->registry->register($key, $value);
    }

    /**
     * Get value from core registry
     *
     * @param mixed $key
     * @return mixed
     */
    public function getValue($key)
    {
        return $this->registry->registry($key);
    }
}
