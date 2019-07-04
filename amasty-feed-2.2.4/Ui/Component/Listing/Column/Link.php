<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Ui\Component\Listing\Column;

use Amasty\Feed\Model\Feed;

class Link extends Action
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['entity_id'])) {
                    $storeId = isset($item['orig_store_id']) ? $item['orig_store_id'] : $item['store_id'];
                    $link = $this->getDownloadHref($item['entity_id'], $storeId);
                    $filename = $item['filename'] . '.' . $item['feed_type'];

                    if ($item['status'] == Feed::READY) {
                        $item[$this->getData('name')] =
                            $this->getLinkHtml($link, $filename) . $this->makeCopyToClipboardButton();
                    } else {
                        $item[$this->getData('name')] = $filename;
                    }

                }
            }
        }

        return $dataSource;
    }

    /**
     * @return string
     */
    private function makeCopyToClipboardButton()
    {
        return '<button class="button action primary amasty-copy-on-clipboard-button">' . __('Copy Link') . '</button>';
    }

    /**
     * @param string $link
     * @param string $filename
     *
     * @return string
     */
    private function getLinkHtml($link, $filename)
    {
        return '<a class="amasty-copy-on-clipboard-text" target="_blank" href="' . $link . '">' . $filename . '</a>';
    }

    /**
     * @param int $feedId
     * @param int $storeId
     *
     * @return string
     */
    private function getDownloadHref($feedId, $storeId)
    {
        $urlInstance = $this->getUrlInstance();

        $routeParams = [
            '_direct' => 'amfeed/feed/download',
            '_query' => [
                'id' => $feedId
            ]
        ];

        $href = $urlInstance
            ->setScope($storeId)
            ->getUrl(
                '',
                $routeParams
            );

        return $href;
    }
}
