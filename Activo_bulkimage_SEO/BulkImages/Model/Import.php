<?php
/**
 * Activo Extensions
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Activo Commercial License
 * that is available through the world-wide-web at this URL:
 * http://extensions.activo.com/license_professional
 *
 * @copyright   Copyright (c) 2017 Activo Extensions (http://extensions.activo.com)
 * @license     Commercial
 */
namespace Activo\BulkImages\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Activo\BulkImages\Model\ResourceModel\Import\Collection;
use Activo\BulkImages\Helper\Data;

class Import extends \Magento\Framework\Model\AbstractModel
{

    protected $separator = null;
    protected $advancedLogging = false;
    protected $products = [];
    protected $files = [];
    protected $imageExtensions = ['jpg', 'png', 'gif'];

    const PATH_LOG_FILE = 'activo_bulkimages.log';
    const CPATH_SOURCE_FOLDER = 'activo_bulkimages/global/sourcefolder';
    const CPATH_SUBFOLDERS = 'activo_bulkimages/global/subfolders';
    const CPATH_SEPARATOR = 'activo_bulkimages/global/separator';
    const CPATH_EXCLUDE_FIRST = 'activo_bulkimages/global/excludefirst';
    const CPATH_DELETE_OLD = 'activo_bulkimages/global/deleteold';
    const CPATH_FILTER_OPTIONS = 'activo_bulkimages/global/filteroptions';
    const CPATH_ATTACH_SIMPLE = 'activo_bulkimages/global/attachtosimple';
    const CPATH_LOGGING = 'activo_bulkimages/global/logging';
    const CPATH_REGEX_PATTERN = 'activo_bulkimages/regex/regexpattern';
    const CPATH_REGEX_REPLACE = 'activo_bulkimages/regex/regexreplace';
    const CPATH_SEO_FILENAME = 'activo_bulkimages/seo/newfilename';
    const CPATH_UPLOAD_FOLDER = 'activo_bulkimages/dragndrop/uploadfolder';
    const CPATH_REMOVE_AFTER = 'activo_bulkimages/dragndrop/removeafterupload';
    const FILTER_OPTIONS_ALL = 1;
    const FILTER_OPTIONS_VISIBLE_SEARCH_CATALOG = 2;
    const FILTER_OPTIONS_VISIBLE_SEARCH = 3;
    const FILTER_OPTIONS_VISIBLE_CATALOG = 4;
    const FILENAME_OPTIONS_NOCHANGE = 1;
    const FILENAME_OPTIONS_NAME = 2;
    const FILENAME_OPTIONS_NAME_SKU = 3;
    const FILENAME_OPTIONS_SKU_NAME = 4;

    protected $messageManager;
    protected $fileSystem;
    protected $resourceIterator;
    protected $productConfig;
    protected $logger;
    protected $prodCollection;
    protected $acitvoImportCollection;
    protected $activoHelper;
    protected $scopeConfig;
    protected $import;
    protected $productFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator,
        \Activo\BulkImages\Logger\Logger $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Catalog\Model\Product\Media\Config $productConfig,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        Collection $acitvoImportCollection,
        CollectionFactory $prodCollection,
        Data $activoHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        $data = []
    ) {
        $this->resourceIterator = $resourceIterator;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->prodCollection = $prodCollection;
        $this->activoHelper = $activoHelper;
        $this->fileSystem = $fileSystem;
        $this->productConfig = $productConfig;
        $this->productFactory = $productFactory;        
        $this->acitvoImportCollection = $acitvoImportCollection;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Activo\BulkImages\Model\ResourceModel\Import');
        $this->advancedLogging = $this->activoHelper->getStoreConfig(self::CPATH_LOGGING);
        $this->separator = $this->activoHelper->getStoreConfig(self::CPATH_SEPARATOR);
    }

    public function processImport($processAllFiles = true, $disableMessages = false, $isDragnDrop = false, $removeAfter = false)
    {

        if ($this->advancedLogging) {
            $this->logger->info('--> Started bulk images import process...');
        }
        $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);

        // Get running parameters, last import date and recentImportId
        // If this is DragAndDrop action, use upload folder, otherwise standard source folder
        if ($isDragnDrop) {
            // Make sure we clean directory separators from the base media folder
            // and the import folder as users may or may not enter training slashes properly...
            $importFolder = $mediaDirectory->getAbsolutePath(ltrim($this->activoHelper->getStoreConfig(self::CPATH_UPLOAD_FOLDER)));
            $log_message = "Starting Drag-and-Drop Import Process";
        } else {
            $importFolder = $mediaDirectory->getAbsolutePath(ltrim($this->activoHelper->getStoreConfig(self::CPATH_SOURCE_FOLDER)));
            $log_message = "Starting Regular Import Process";
        }
        $removeAfter = $this->activoHelper->getStoreConfig(self::CPATH_REMOVE_AFTER);
        $recentImportTime = self::_getRecentImportTime();
        $newImportIdNum = (string) (self::_getRecentImportId() + 1);
        $newImportId = '.' . $newImportIdNum;
        $numImages = 0;
        $numSkuMatches = 0;
        $numImageMatches = 0;
        $errors = '';
        $status = 'ok';

        if ($this->advancedLogging) {
            $this->logger->info($log_message);
            $this->logger->info('Import folder: ' . $importFolder);
            $this->logger->info('Last import time: ' . date('c', $recentImportTime));
            if ($processAllFiles) {
                $this->logger->info('Processing all files');
            } else {
                $this->logger->info('Processing only files modified after last import time');
            }
            
            $this->logger->info('New import ID: ' . $newImportIdNum);
        }

        // Build an array of available image files and matching SKUs
        if (!is_dir($importFolder)) {
            //show an error message: please correct dir path.
            if (!$disableMessages) {
                $this->messageManager->addErrorMessage(__('Error: the entered import folder is invalid.'));
                $errors .= __('Error: the entered import folder is invalid.');
                $status = 'error';
            }
            
            if ($this->advancedLogging) {
                $this->logger->info('Error: the import folder does not exist: ' . $importFolder);
            }
        } else {
            // Check if we need to search directory recursively or only one level directory
            $recursive = $this->activoHelper->getStoreConfig(self::CPATH_SUBFOLDERS);
            if ($recursive) {
                $di = new RecursiveDirectoryIterator($importFolder);
                $iterator = new RecursiveIteratorIterator($di);
                if ($this->advancedLogging) {
                    $this->logger->info('Also including subfolders...');
                }
            } else {
                $di = new \DirectoryIterator($importFolder);
                $iterator = new \IteratorIterator($di);
            }

            if ($this->advancedLogging) {
                $this->logger->info('Searching for available image files. Import folder: ' . $importFolder);
            }

            foreach ($iterator as $ff => $file) {
                $filename = $file->getPathname();
// echo $filename;die;
                // ignore file it is not one of the supported image extensions
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if (!in_array(strtolower($ext), $this->imageExtensions)) {
                    continue;
                }

                $fname = $file->getFilename();
                $fbase = $file->getBasename('.' . $ext);

                $spos = stripos($fbase, $this->separator);

                // If file does not have a peapartor string, use entire file name as SKU
                if (false === $spos) {
                    $sku = $fbase;
                    $num = '0';
                } else {
                    $sku = substr($fbase, 0, $spos);
                    $num = substr($fbase, $spos + strlen($this->separator));
                }

                if (!empty($sku)) {
                    $this->files[$sku][$num]['file'] = $fname;
                    $this->files[$sku][$num]['srcfile'] = $filename;

                    if ($processAllFiles || ($file->getMTime() > $recentImportTime) || ($file->getCTime() > $recentImportTime)) {
                        $this->files[$sku]['process'] = true;
                        $proc = 'IMPORT';
                        $numImages++;
                    } else {
                        $proc = 'old file, ignore';
                    }

                    if ($this->advancedLogging) {
                        $this->logger->info(sprintf('Found image file: %s -> sku: %s, sort: %s (%s)', $filename, $sku, $num, $proc));
                    }
                }
            }

            // get array of visible products with: sku, runway_brand, gallery images
            $arrayAttrs = ['name', 'image', 'small_image', 'thumbnail'];

            // Default: ALL Products
            $pCollection = $this->prodCollection->create();
            $pCollection->addAttributeToSelect('sku')
                ->addAttributeToSelect('entity_type_id')
                ->addAttributeToSelect($arrayAttrs, 'left');

            switch ($this->activoHelper->getStoreConfig(self::CPATH_FILTER_OPTIONS)) {
                case self::FILTER_OPTIONS_VISIBLE_SEARCH_CATALOG:
                    $pCollection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
                    break;
                case self::FILTER_OPTIONS_VISIBLE_SEARCH:
                    $pCollection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH);
                    break;
                case self::FILTER_OPTIONS_VISIBLE_CATALOG:
                    $pCollection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG);
                    break;
                case self::FILTER_OPTIONS_ALL:
                default:
                    break;
            }

            // Build $this->_products array by walking through the catalog and applying the RegEx if necessary
            if ($this->advancedLogging) {
                $this->logger->info('Searching for available product SKUs... ');
            }

            $this->resourceIterator->walk(
                $pCollection->getSelect(), [[$this, 'productCallback']], ['arg1' => '====']
            );

            $_productsArray = $this->products;
            unset($pCollection);

            //io object
            $io = new \Magento\Framework\Filesystem\Io\File;
            $io->setAllowCreateFolders(true);

            // loop through files, if file name matches a product, do:
            if ($this->advancedLogging) {
                $this->logger->info('Checking files for SKU matches...');
            }
            foreach ($this->files as $sku => $fileArray) {
                if (isset($fileArray['process'])) {
                    unset($fileArray['process']);

                    // if filename matches product sku
                    if (isset($_productsArray[$sku])) {
                        $numSkuMatches++;
                        //The product object
                        $a = $_productsArray[$sku];
                        if ($this->advancedLogging) {
                            $this->logger->info(sprintf('Matched image to SKU: %s', $sku));
                        }

                        foreach ($a as $pId) {
                            // Load new instance for each product 
                            $p = $this->productFactory->create()->load($pId);

                            //The array of old image files for this sku
                            $imagesOld = self::_getImageFilesArray($p);

                            if ($this->activoHelper->getStoreConfig(self::CPATH_DELETE_OLD)) {
                                if ($this->advancedLogging) {
                                    $this->logger->info(' - removing old images for SKU: ' . $p->getSku());
                                }

                                //remove old image files
                                foreach ($imagesOld as $image) {
                                    $imagePath = $this->_getMediaConfig()->getMediaPath($image);
                                    $io->rm($imagePath);
                                    if ($this->advancedLogging) {
                                        $this->logger->info(' - removed image: ' . $imagePath);
                                    }
                                }

                                //delete media gallery db records for product
                                $this->getResource()->deleteMediaGalleryByProductId($p);

                                // Start new image import number from 1
                                $n = 1;
                            } else {
                                $n = count($imagesOld) + 1;
                            }

                            //sort filesArray
                            ksort($fileArray);
                            foreach ($fileArray as $fc => $f) {
                                $numImageMatches++;
                                $this->files[$sku][$fc]['match'] = true;

                                $newfile = $this->_getNewFileName($f['file'], $newImportId, $p);
                                $newpath = $this->_getNewFilePath(substr($newfile, 5));
                                $newpath = $mediaDirectory->getAbsolutePath($newpath);
                                $mediapath = $this->_getMediaConfig()->getMediaPath($newfile);
                                $mediapath = $mediaDirectory->getAbsolutePath($mediapath);

                                if ($this->advancedLogging) {
                                    $this->logger->info(' - importing image file: ' . $f['srcfile'] . '  ->  ' . $mediapath);
                                }

                                $io->open(['path' => $newpath]);
                                $io->cp($f['srcfile'], $mediapath);

                                //enter value in DB
                                $this->getResource()->addImage(
                                    $newfile, $p, $n, $this->activoHelper->getStoreConfig(self::CPATH_EXCLUDE_FIRST)
                                );

                                //If option enabled in config AND isConfigurable AND first image
                                if ($this->activoHelper->getStoreConfig(self::CPATH_ATTACH_SIMPLE) &&
                                    ($p->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) &&
                                    $n == 1) {
                                    $assocProducts = $p->getTypeInstance()->getUsedProducts($p);
                                    foreach ($assocProducts as $aProduct) {
                                        //delete media gallery db records for product ID
                                        $this->getResource()->deleteMediaGalleryByProductId($aProduct);

                                        //associate the first image only
                                        $this->getResource()->addImage(
                                            $newfile, $aProduct, $n, $this->activoHelper->getStoreConfig(self::CPATH_EXCLUDE_FIRST)
                                        );
                                    }
                                }

                                //increment local counter of position
                                $n++;
                            }
                        }
                    }
                }
            }

            $this->setData(['num_images' => $numImages,
                'num_images_unmatched' => ($numImages - $numImageMatches),
                'num_skus' => (count($this->products)),
                'num_skus_unmatched' => (count($this->products) - $numSkuMatches),
                'num_matches' => $numSkuMatches,
                'created_at' => date('Y-m-d H:i:s'),
                'success_full' => 1]);
            
            $this->_getResource()->save($this);

            if ($this->advancedLogging) {
                $this->logger->info(sprintf('Processed %s image files, matched %s images, %s product SKUs.', $numImages, $numImageMatches, $numSkuMatches));
            }

            // Remove images from uploads folder after import if necessary

            if ($isDragnDrop && $removeAfter) {
                $_files = glob($importFolder . '/' . '*');
                foreach ($_files as $file) {
                    if (is_file($file)) {
                        unlink($file); // delete file
                    }
                }     
                if ($this->advancedLogging) {
                    $this->logger->info('Removing files from uploads directory: ' . $importFolder . '/' . '*');
                }
            }

            //Show success message
            if (!$disableMessages) {
                $this->messageManager->addSuccessMessage(__('Successfully imported ' . $numImageMatches . ' images.'));
            }
        }

        if ($this->advancedLogging) {
            // Now let's generate a list of unmatched files:
            $buff = '';
            foreach ($this->files as $sku => $ff) {
                if (empty($ff['process'])) {
                    continue;
                }
                
                foreach ($ff as $fc => $f) {
                    if ($fc === 'process') {
                        continue;
                    }
                    
                    if (!empty($f['match'])) {
                        continue;
                    }
                    
                    $buff .= "\n  " . $f['srcfile'];
                }
            }
            
            if (!empty($buff)) {
                $this->logger->info('Unmatched image files: ' . $buff);
            }

            $this->logger->info('Finished bulk images import process...');
            $this->logger->info('-----' . PHP_EOL);
        }

        return compact('status', 'numImages', 'numImageMatches', 'numSkuMatches', 'errors');
    }

    public function productCallback($args)
    {
        $sku = $args['row']['sku'];

        $regexPattern = '';
        $regexReplace = '';
        if ($this->activoHelper->getStoreConfig(self::CPATH_REGEX_PATTERN) != '') {
            $regexPattern = $this->activoHelper->getStoreConfig(self::CPATH_REGEX_PATTERN);
            $regexReplace = $this->activoHelper->getStoreConfig(self::CPATH_REGEX_REPLACE);
        }

        if ($this->advancedLogging) {
            $this->logger->info('Found product SKU: ' . $sku);
        }

        $skuNew = $sku;
        if (!empty($regexPattern)) {
            $skuNew = preg_replace($regexPattern, $regexReplace, $sku);
            if ($this->advancedLogging) {
                $this->logger->info('Applied RexEx pattern to SKU: ' . $sku . '  ->  ' . $skuNew);
            }
        }

        if (!isset($this->products[$skuNew])) {
            $this->products[$skuNew] = [];
        }

        $this->products[$skuNew][] = $args['row']['entity_id'];
    }

    protected function _getNewFileName($file, $newImportId, $product)
    {
        // Maximum image file length -- has to be lesser that 250 characters reserved for the record in DB
        $maxsize = 230;
        $extension = substr($file, stripos($file, $this->_getSeparator()) + strlen($this->_getSeparator()));
        $maxsize -= strlen($extension);

        switch ($this->activoHelper->getStoreConfig(self::CPATH_SEO_FILENAME)) {
            case self::FILENAME_OPTIONS_NAME:
                $newfile = $product->getName() . '-';
                $newfile = substr($newfile, 0, $maxsize) . $extension;
                break;

            case self::FILENAME_OPTIONS_NAME_SKU:
                $newfile = $product->getName() . '-' . $product->getSku() . '-';
                $newfile = substr($newfile, 0, $maxsize) . $extension;
                break;

            case self::FILENAME_OPTIONS_SKU_NAME:
                $newfile = $product->getSku() . '-' . $product->getName() . '-';
                $newfile = substr($newfile, 0, $maxsize) . $extension;
                break;

            case self::FILENAME_OPTIONS_NOCHANGE:
            default:
                $newfile = $file;
                break;
        }

        $newfile = $this->sanitize_file_name($newfile);

        $endFilename = $this->_getEndFilenamePosition($newfile);
        $newfile = '/' . substr($newfile, 0, 1) . '/' . substr($newfile, 1, 1)
            . '/' . substr_replace($newfile, $newImportId, $endFilename, 0);
        $newfile = strtolower($newfile);

        return $newfile;
    }

    protected function _getNewFilePath($file)
    {
        $newpath = substr($file, 0, 1) . '/' . substr($file, 1, 1) . '/';
        $newpath = $this->_getMediaConfig()->getBaseMediaPath() . '/' . strtolower($newpath);
        return $newpath;
    }

    protected function sanitize_file_name($filename)
    {
        $filenameRaw = $filename;
        $specialChars = ["?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'",
            "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", "%"];
        $filename = str_replace($specialChars, '', $filename);
        $filename = preg_replace('/[\s-]+/', '-', $filename);
        $filename = trim($filename, '.-_');
        return $filename;
    }

    protected function _getEndFilenamePosition($file)
    {
        $imageExtensions = ['.jpg', '.png', '.gif'];
        $pos = false;

        foreach ($imageExtensions as $imageExtension) {
            $pos = stripos($file, $imageExtension);
            if ($pos !== false) {
                break;
            }
        }

        return $pos;
    }

    protected function _getMediaConfig()
    {
        return $this->productConfig;
    }

    protected function _getImageFilesArray($product)
    {
        $imageFilesArray = [];
        $heystack = '';

        if ($product->getImage() != '') {
            $imageFilesArray[] = $product->getImage();
            $heystack = $product->getImage();
        }
        
        if ($product->getSmallImage() != '' && stripos($heystack, $product->getSmallImage()) === false) {
            $imageFilesArray[] = $product->getSmallImage();
            $heystack .= $product->getSmallImage();
        }
        
        if ($product->getThumbnail() != '' && stripos($heystack, $product->getThumbnail()) === false) {
            $imageFilesArray[] = $product->getThumbnail();
            $heystack .= $product->getThumbnail();
        }
        
        //get all media gallery images
        if ($product->getMediaGalleryImages() != null) {
            foreach ($product->getMediaGalleryImages() as $image) {
                if (stripos($heystack, $image->getFile()) === false) {
                    $imageFilesArray[] = $image->getFile();
                    $heystack .= $image->getFile();
                }
            }
        }

        return $imageFilesArray;
    }

    protected function _getSkuFromFilename($filename)
    {
        $endPos = stripos($filename, $this->_getSeparator());

        if ($endPos === false) {
            return false;
        } else {
            return substr($filename, 0, $endPos);
        }
    }

    protected function _getNumFromFilename($filename)
    {
        $endPos = stripos($filename, $this->_getSeparator());

        if ($endPos === false) {
            return false;
        } else {
            return substr($filename, $endPos + strlen($this->_getSeparator()), -4);
        }
    }

    protected function _getRecentImportTime()
    {
        $lastImport = $this->acitvoImportCollection->getLastItem();

        if ($lastImport === null) {
            return 0;
        } else {
            return strtotime($lastImport->getCreatedAt());
        }
    }

    protected function _getRecentImportId()
    {
        $lastImport = $this->acitvoImportCollection->getLastItem();

        if ($lastImport === null) {
            return 0;
        } else {
            return $lastImport->getId();
        }
    }

    protected function _getSeparator()
    {
        if ($this->separator == null) {
            $this->separator = $this->activoHelper->getStoreConfig(self::CPATH_SEPARATOR);
        }

        return $this->separator;
    }

    public function getObjectManager()
    {
        return \Magento\Framework\App\ObjectManager::getInstance();
    }
}
