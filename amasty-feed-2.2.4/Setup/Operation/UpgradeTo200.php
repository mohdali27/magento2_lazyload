<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Setup\Operation;

use Magento\Email\Model\Template;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Dir;
use Magento\Framework\Exception\FileSystemException;

/**
 * Class UpgradeTo200
 */
class UpgradeTo200
{
    const MODULE_DIR = 'Amasty_Feed';
    const SUCCESS_TEMPLATE_NAME = 'amasty_feed_notifications_success_template';
    const SUCCESS_TEMPLATE_SUBJECT = 'Amasty Succsessfull Feed Generation';
    const UNSUCCESS_TEMPLATE_NAME = 'amasty_feed_notifications_unsuccess_template';
    const UNSUCCESS_TEMPLATE_SUBJECT  = 'Amasty Unsuccsessfull Feed Generation';
    const TEMPLATE_VARIABLES = 'amasty_feed_notifications_generation_variables';

    /**
     * @var Template
     */
    private $template;

    /**
     * @var File
     */
    private $filesystem;

    /**
     * @var Reader
     */
    private $moduleReader;

    public function __construct(
        Template $template,
        File $filesystem,
        Reader $moduleReader
    ) {
        $this->template = $template;
        $this->filesystem = $filesystem;
        $this->moduleReader = $moduleReader;
    }

    /**
     * @throws FileSystemException
     */
    public function execute()
    {
        $templateVars = $this->filesystem->fileGetContents($this->getDirectory(self::TEMPLATE_VARIABLES . '.html'));
        $this->createGenerationEmailTemplate($templateVars, self::SUCCESS_TEMPLATE_NAME,  self::SUCCESS_TEMPLATE_SUBJECT);
        $this->createGenerationEmailTemplate($templateVars, self::UNSUCCESS_TEMPLATE_NAME, self::UNSUCCESS_TEMPLATE_SUBJECT);
    }

    /**
     * @param string $templateVars
     * @param string $templateName
     * @param string $templateSubject
     * @throws FileSystemException
     */
    private function createGenerationEmailTemplate($templateVars, $templateName, $templateSubject)
    {
        $templateText = $this->filesystem->fileGetContents($this->getDirectory($templateName . '.html'));
        $templateData = [
            'template_code' => $templateSubject,
            'template_subject' => $templateSubject,
            'template_type' => Template::TYPE_HTML,
            'template_text' => $templateText,
            'orig_template_variables' => $templateVars,
            'orig_template_code' => $templateName
        ];
        $this->template->setData($templateData)
            ->save();
    }

    /**
     * @param string $templateName
     * @return string
     */
    private function getDirectory($templateName)
    {
        $viewDir = $this->moduleReader->getModuleDir(
            Dir::MODULE_VIEW_DIR,
            self::MODULE_DIR
        );

        return $viewDir . '/frontend/email/' . $templateName;
    }
}
