<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Block\Product\Edit\Downloadable;

use Magento\Downloadable\Model\Product\Type;

class Samples extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_marketplaceHelper;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_dataObject;

    /**
     * @var \Magento\Downloadable\Helper\File
     */
    protected $_downloadableHelperFile = null;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Downloadable\Model\Sample
     */
    protected $_downloadableSample;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonHelperData;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\Marketplace\Helper\Data                  $marketplaceHelper
     * @param \Magento\Framework\DataObject                    $dataObject
     * @param \Magento\Framework\Json\Helper\Data              $jsonHelperData
     * @param \Magento\Downloadable\Helper\File                $downloadableHelperFile
     * @param \Magento\Framework\Registry                      $registry
     * @param \Magento\Downloadable\Model\Sample               $downloadableSample
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Framework\DataObject $dataObject,
        \Magento\Framework\Json\Helper\Data $jsonHelperData,
        \Magento\Downloadable\Helper\File $downloadableHelperFile,
        \Magento\Framework\Registry $registry,
        \Magento\Downloadable\Model\Sample $downloadableSample,
        array $data = []
    ) {
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_dataObject = $dataObject;
        $this->_jsonHelperData = $jsonHelperData;
        $this->_registry = $registry;
        $this->_downloadableHelperFile = $downloadableHelperFile;
        $this->_downloadableSample = $downloadableSample;
        parent::__construct(
            $context,
            $data
        );
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getSellerProduct()
    {
        return $this->_registry->registry('product');
    }

    public function getFileUploaderJsonData()
    {
        $data = [
            'url' => $this->_urlBuilder->getUrl(
                'marketplace/product_downloadable_file/upload',
                ['type' => 'samples', '_secure' => $this->getRequest()->isSecure()]
            ),
            'width' => '32',
            'params' => ['form_key' => $this->getFormKey()],
            'filters' => ['all' => ['label' => __('All Files'), 'files' => ['*.*']]],
            'file_field' => 'samples',
            'replace_browse_with_remove' => true,
            'hide_upload_button' => true,
        ];
        $this->_dataObject->setData($data);

        return $this->_jsonHelperData->jsonEncode(
            $this->_dataObject->getData()
        );
    }

    /**
     * @return array
     */
    public function getDownloadableSampleInfo()
    {
        $sampleArrObj = [];
        if ($this->getSellerProduct()->getTypeId() !== Type::TYPE_DOWNLOADABLE) {
            return $sampleArrObj;
        }
        $sampleData = $this->getSellerProduct()->getTypeInstance()->getSamples(
            $this->getSellerProduct()
        );
        $websitePriceStatus = $this->_marketplaceHelper->getConfigPriceWebsiteScope();
        $fileHelper = $this->_downloadableHelperFile;
        foreach ($sampleData as $sample) {
            $sampleId = $sample->getId();
            $getsampleInfo = $sample->getSampleFile();
            $sampleArr = [
                'sample_id' => $sampleId,
                'title' => $this->escapeHtml($sample->getTitle()),
                'sample_url' => $sample->getSampleUrl(),
                'sample_type' => $sample->getSampleType(),
                'sort_order' => $sample->getSortOrder(),
            ];
            if ($sample->getStoreTitle()) {
                $sampleArr['store_title'] = $sample->getStoreTitle();
            }

            $getsampleInfo = $sample->getSampleFile();
            if ($getsampleInfo) {
                $sampleFilePath = $fileHelper->getFilePath(
                    $this->_downloadableSample->getBasePath(),
                    $getsampleInfo
                );
                $sampleFilePathExist = $fileHelper->ensureFileInFilesystem($sampleFilePath);
                if ($sampleFilePathExist) {
                    $fileName = '<a href="'.$this->_urlBuilder->getUrl(
                        'marketplace/product_downloadable_product_edit/sample',
                        [
                            'type' => 'sample',
                            'id' => $sampleId,
                            '_secure' => $this->getRequest()->isSecure(),
                        ]
                    ).'">'.$fileHelper->getFileFromPathFile($getsampleInfo).'</a>';
                    $sampleArr['file_save'] = [
                        [
                            'name' => $fileName,
                            'file' => $getsampleInfo,
                            'size' => $fileHelper->getFileSize($sampleFilePath),
                            'status' => 'old',
                        ],
                    ];
                }
            }
            $sampleArrObj[] = new \Magento\Framework\DataObject($sampleArr);
        }

        return $sampleArrObj;
    }
}
