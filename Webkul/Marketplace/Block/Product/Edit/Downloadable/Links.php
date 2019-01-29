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

class Links extends \Magento\Framework\View\Element\Template
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
     * @var \Magento\Downloadable\Model\Link
     */
    protected $_downloadableLink;

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
     * @param \Magento\Downloadable\Model\Link                 $downloadableLink
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Framework\DataObject $dataObject,
        \Magento\Framework\Json\Helper\Data $jsonHelperData,
        \Magento\Downloadable\Helper\File $downloadableHelperFile,
        \Magento\Framework\Registry $registry,
        \Magento\Downloadable\Model\Link $downloadableLink,
        array $data = []
    ) {
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_dataObject = $dataObject;
        $this->_jsonHelperData = $jsonHelperData;
        $this->_registry = $registry;
        $this->_downloadableHelperFile = $downloadableHelperFile;
        $this->_downloadableLink = $downloadableLink;
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

    public function getFileUploaderJsonData($type = 'links')
    {
        $data = [
            'url' => $this->_urlBuilder->getUrl(
                'marketplace/product_downloadable_file/upload',
                ['type' => $type, '_secure' => $this->getRequest()->isSecure()]
            ),
            'width' => '32',
            'params' => ['form_key' => $this->getFormKey()],
            'file_field' => $type,
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
    public function getDownloadableLinkInfo()
    {
        $linkArrObj = [];
        if ($this->getSellerProduct()->getTypeId() !== Type::TYPE_DOWNLOADABLE) {
            return $linkArrObj;
        }
        $linkData = $this->getSellerProduct()->getTypeInstance()->getLinks(
            $this->getSellerProduct()
        );
        $websitePriceStatus = $this->_marketplaceHelper->getConfigPriceWebsiteScope();
        $fileHelper = $this->_downloadableHelperFile;
        foreach ($linkData as $link) {
            $linkId = $link->getId();
            $getsampleInfo = $link->getSampleFile();
            $linkArr = [
                'link_id' => $linkId,
                'title' => $this->escapeHtml($link->getTitle()),
                'price' => number_format($link->getPrice(), 2, null, ''),
                'link_url' => $link->getLinkUrl(),
                'link_type' => $link->getLinkType(),
                'is_shareable' => $link->getIsShareable(),
                'sample_file' => $getsampleInfo,
                'sample_url' => $link->getSampleUrl(),
                'sample_type' => $link->getSampleType(),
                'sort_order' => $link->getSortOrder(),
                'number_of_downloads' => $link->getNumberOfDownloads(),
            ];
            if ($link->getStoreTitle()) {
                $linkArr['store_title'] = $link->getStoreTitle();
            }
            if ($link->getNumberOfDownloads() == '0') {
                $linkArr['is_unlimited'] = ' checked="checked"';
            }
            if ($websitePriceStatus) {
                $linkArr['website_price'] = $link->getWebsitePrice();
            }

            $getlinkInfo = $link->getLinkFile();
            if ($getlinkInfo) {
                $linkFilePath = $fileHelper->getFilePath(
                    $this->_downloadableLink->getBasePath(),
                    $getlinkInfo
                );
                $linkFilePathExist = $fileHelper->ensureFileInFilesystem($linkFilePath);
                if ($linkFilePathExist) {
                    $fileName = '<a href="'.$this->_urlBuilder->getUrl(
                        'marketplace/product_downloadable_product_edit/link',
                        [
                            'type' => 'link',
                            'id' => $linkId,
                            '_secure' => $this->getRequest()->isSecure(),
                        ]
                    ).'">'.$fileHelper->getFileFromPathFile($getlinkInfo).'</a>';
                    $linkArr['file_save'] = [
                        [
                            'name' => $fileName,
                            'file' => $getlinkInfo,
                            'size' => $fileHelper->getFileSize($linkFilePath),
                            'status' => 'old',
                        ],
                    ];
                }
            }

            if ($getsampleInfo) {
                $sampleFilePath = $fileHelper->getFilePath(
                    $this->_downloadableLink->getBaseSamplePath(),
                    $getsampleInfo
                );
                $getsampleInfoExist = $fileHelper->ensureFileInFilesystem($sampleFilePath);
                if ($getsampleInfoExist) {
                    $fileName = '<a href="'.$this->_urlBuilder->getUrl(
                        'marketplace/product_downloadable_product_edit/link',
                        [
                            'type' => 'sample',
                            'id' => $linkId,
                            '_secure' => $this->getRequest()->isSecure(),
                        ]
                    ).'">'.$fileHelper->getFileFromPathFile(
                        $getsampleInfo
                    ).'</a>';
                    $linkArr['sample_file_save'] = [
                        [
                            'name' => $fileName,
                            'file' => $getsampleInfo,
                            'status' => 'old',
                            'size' => $fileHelper->getFileSize($sampleFilePath),
                        ],
                    ];
                }
            }
            $linkArrObj[] = new \Magento\Framework\DataObject($linkArr);
        }

        return $linkArrObj;
    }
}
