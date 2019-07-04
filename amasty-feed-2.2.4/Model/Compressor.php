<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model;

class Compressor extends \Magento\Framework\DataObject
{
    /** @var \Magento\Framework\Archive\ArchiveInterface */
    protected $compressor;

    /**
     * @param \Magento\Framework\Archive\ArchiveInterface $compressor
     */
    public function __construct(
        \Magento\Framework\Archive\ArchiveInterface $compressor
    ) {
        $this->compressor = $compressor;
    }

    /**
     * Pack file or directory.
     *
     * @param string $source
     * @param string $destination
     * @param string $filename
     *
     * @return string
     */
    public function pack($source, $destination, $filename)
    {
        if ($this->compressor instanceof \Magento\Framework\Archive\Zip) {
            $zip = new \ZipArchive();
            $zip->open($destination, \ZipArchive::CREATE);
            $zip->addFile($source, $filename);
            $zip->close();
        } else {
            $destination = $this->compressor->pack($source, $destination);
        }

        return $destination;
    }
}
