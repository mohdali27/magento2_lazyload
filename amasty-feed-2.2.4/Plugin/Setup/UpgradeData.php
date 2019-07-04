<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Plugin\Setup;

class UpgradeData
{
    /**
     * @var \Amasty\Base\Setup\SerializedFieldDataConverter
     */
    private $fieldDataConverter;

    public function __construct(\Amasty\Base\Setup\SerializedFieldDataConverter $fieldDataConverter)
    {
        $this->fieldDataConverter = $fieldDataConverter;
    }

    /**
     * @param \Magento\SalesRule\Setup\UpgradeData $subject
     * @param $result
     * @return mixed
     */
    public function afterConvertSerializedDataToJson(\Magento\SalesRule\Setup\UpgradeData $subject, $result)
    {
        $this->fieldDataConverter->convertSerializedDataToJson(
            'amasty_feed_entity',
            'entity_id',
            ['conditions_serialized']
        );

        return $result;
    }
}
