<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;

class Connection extends Action
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Magento\Framework\Filesystem\Io\Ftp
     */
    private $ftp;

    /**
     * @var \Magento\Framework\Filesystem\Io\Sftp
     */
    private $sftp;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $metadata;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Filesystem\Io\Ftp\Proxy $ftp,
        \Magento\Framework\Filesystem\Io\Sftp\Proxy $sftp,
        \Magento\Framework\App\ProductMetadataInterface $metadata
    ) {
        parent::__construct($context);
        $this->jsonHelper = $jsonHelper;
        $this->ftp = $ftp;
        $this->sftp = $sftp;
        $this->metadata = $metadata;
    }

    /**
     * @return \Magento\Framework\App\Response\Http
     */
    public function execute()
    {
        try {
            $this->testConnection();
        } catch (\Exception $error) {
            return $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode(['type' => 'error', 'message' => __($error->getMessage())])
            );
        }

        return $this->getResponse()->representJson($this->jsonHelper->jsonEncode(__('Success!')));
    }

    /**
     * @throws LocalizedException
     */
    private function testConnection()
    {
        $params = $this->getRequest()->getParams();
        if (!$params) {
            throw new LocalizedException(__('Request params is empty'));
        }
        //Generate random .tmp file name to check write permissions
        $this->fileName = md5(uniqid(rand(), true)) . '.tmp';

        if ($params['proto'] === 'ftp') {
            $this->testFtpConnection($params);
        } elseif ($params['proto'] === 'sftp') {
            $this->testSftpConnection($params);
        } else {
            throw new LocalizedException(__('Invalid protocol'));
        }
    }

    /**
     * @param array $params
     *
     * @throws LocalizedException
     */
    private function testFtpConnection($params)
    {
        if (strpos($params['host'], ':') !== false) {
            list($host, $port) = explode(':', $params['host'], 2);
        } else {
            $host = $params['host'];
            $port = null;
        }

        $this->ftp->open(
            [
                'host' => $host,
                'port' => $port,
                'user' => $params['user'],
                'password' => $params['pass'],
                'passive' => $params['mode'],
                'path' => $params['path']
            ]
        );

        if (!$this->ftp->write($this->fileName, (string)__('Amasty Feed test connection file!'))) {
            $this->ftp->close();
            throw new LocalizedException(__('No write permissions'));
        }
        $this->ftp->rm($this->fileName);

        $this->ftp->close();
    }

    /**
     * @param array $params
     *
     * @throws LocalizedException
     */
    private function testSftpConnection($params)
    {
        if (version_compare($this->metadata->getVersion(), '2.2.0', '<')) {
            /** Fix for Magento <2.2.0 versions @see https://github.com/magento/magento2/issues/9016 */
            define('NET_SFTP_LOCAL_FILE', \phpseclib\Net\SFTP::SOURCE_LOCAL_FILE);
            define('NET_SFTP_STRING', \phpseclib\Net\SFTP::SOURCE_STRING);
        }

        $this->sftp->open(
            [
                'host' => $params['host'],
                'username' => $params['user'],
                'password' => $params['pass']
            ]
        );

        $path = $this->sftp->cd($params['path']);
        if (!$path) {
            $this->sftp->close();
            throw new LocalizedException(__('Invalid path'));
        }

        if (!$this->sftp->write($this->fileName, __('Amasty Feed test connection file!'))) {
            $this->sftp->close();
            throw new LocalizedException(__('No write permissions'));
        }
        $this->sftp->rm($this->fileName);

        $this->sftp->close();
    }
}
