<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Setup;

use Amasty\Feed\Model\Import;
use Magento\Framework\Setup;

class Installer implements Setup\SampleData\InstallerInterface
{
    protected $import;

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
        $this->import->install();
    }
}
