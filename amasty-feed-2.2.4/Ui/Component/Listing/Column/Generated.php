<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Ui\Component\Listing\Column;

use Amasty\Feed\Model\Feed;

class Generated extends \Magento\Ui\Component\Listing\Columns\Date
{
    /**#@+
     * Attributes to column
     */
    const READY_ATTRIBUTES = [
        'generated_at' => 'Date',
        'generation_type' => 'Executed',
        'products_amount' => 'Products'
    ];

    const PROCESSING_ATTRIBUTES = [
        'products_amount' => 'Products'
    ];

    const DEFAULT_ATTRIBUTE = [
        'status' => 'Status',
    ];
    /**#@-*/

    /**
     * Status names
     */
    const STATUSES = [
        'Not yet Generated',
        'Ready',
        'Processing',
        'Failed',
    ];

    public function prepareDataSource(array $dataSource)
    {
        parent::prepareDataSource($dataSource);

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item['orig_' . $this->getData('name')] = $item[$this->getData('name')];
                $item[$this->getData('name')] = $this->getColumnValue($item);
            }
        }

        return $dataSource;
    }

    /**
     * @param array $item
     *
     * @return string
     */
    private function getColumnValue($item)
    {
        $result = '';
        $columns = self::DEFAULT_ATTRIBUTE;

        switch ($item['status']) {
            case Feed::READY:
                $columns += self::READY_ATTRIBUTES;
                break;
            case Feed::PROCESSING:
                $columns += self::PROCESSING_ATTRIBUTES;
                break;
        }

        $item['status'] = self::STATUSES[$item['status']];

        foreach ($columns as $key => $value) {
            $result .= __($value) . " : " . __($item[$key]) . "<br/>";
        }

        return $result;
    }
}
