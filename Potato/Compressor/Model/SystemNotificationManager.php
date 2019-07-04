<?php
namespace Potato\Compressor\Model;

use Potato\Compressor\Helper\Data as DataHelper;

class SystemNotificationManager
{
    /** @var DataHelper */
    protected $dataHelper;

    /** @var Config */
    protected $config;

    protected $list = [];

    /**
     * @param DataHelper $dataHelper
     * @param Config $config
     */
    public function __construct(
        DataHelper $dataHelper,
        Config $config
    ) {
        $this->dataHelper = $dataHelper;
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getMessageList()
    {
        $this->list = [];
        $this->checkFolderPermission();
        return $this->list;
    }

    /**
     * @return $this
     */
    protected function checkFolderPermission()
    {
        $path = $this->dataHelper->getRootCachePath();
        @mkdir($path, $this->config->getFolderPermission());
        if (!file_exists($path)) {
            $this->list[] = __('Unable to create folder: %1', $path);
            return $this;
        }
        if (!is_writable($path)) {
            $this->list[] = __('Invalid permissions for folder: %1', $path);
        }
        return $this;
    }
}