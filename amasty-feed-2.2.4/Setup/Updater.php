<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Setup;

use Amasty\Feed\Model\Import;
use Magento\Framework\Setup;

class Updater implements Setup\SampleData\InstallerInterface
{
    protected $import;
    protected $templates = [];

    public function __construct(
        Import $import
    ) {
        $this->import = $import;
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $this->import->update($this->templates);
    }

    public function setTemplates($templates)
    {
        $this->templates = $templates;
    }
}
