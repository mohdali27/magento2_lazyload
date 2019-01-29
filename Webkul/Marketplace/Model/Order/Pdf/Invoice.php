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

namespace Webkul\Marketplace\Model\Order\Pdf;

/**
 * Marketplace Order PDF Invoice model.
 */
class Invoice extends \Magento\Sales\Model\Order\Pdf\Invoice
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $_string;

    /**
     * @param \Webkul\Marketplace\Helper\Data                      $helper
     * @param \Magento\Framework\ObjectManagerInterface            $objectManager,
     * @param \Magento\Payment\Helper\Data                         $paymentData
     * @param \Magento\Framework\Stdlib\StringUtils                $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface   $scopeConfig
     * @param \Magento\Framework\Filesystem                        $filesystem
     * @param Config                                               $pdfConfig
     * @param \Magento\Sales\Model\Order\Pdf\Total\Factory         $pdfTotalFactory
     * @param \Magento\Sales\Model\Order\Pdf\ItemsFactory          $pdfItemsFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Translate\Inline\StateInterface   $inlineTranslation
     * @param \Magento\Sales\Model\Order\Address\Renderer          $addressRenderer
     * @param \Magento\Store\Model\StoreManagerInterface           $storeManager
     * @param \Magento\Framework\Locale\ResolverInterface          $localeResolver
     * @param array                                                $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Webkul\Marketplace\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sales\Model\Order\Pdf\Config $pdfConfig,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->_objectManager = $objectManager;
        $this->_string = $string;
        parent::__construct(
            $paymentData,
            $string,
            $scopeConfig,
            $filesystem,
            $pdfConfig,
            $pdfTotalFactory,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $addressRenderer,
            $storeManager,
            $localeResolver,
            $data
        );
    }

    /**
     * @return \Magento\Framework\Stdlib\StringUtils
     */
    public function getString()
    {
        return $this->_string;
    }

    /**
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->_objectManager;
    }

    /**
     * Return PDF document
     *
     * @param array|Collection $sellerinvoices
     * @return \Zend_Pdf
     */
    public function getPdf($sellerinvoices = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $sellerPdf = new \Zend_Pdf();
        $this->_setPdf($sellerPdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($sellerinvoices as $sellerinvoice) {
            if ($sellerinvoice->getStoreId()) {
                $this->_localeResolver->emulate(
                    $sellerinvoice->getStoreId()
                );
                $this->_storeManager->setCurrentStore(
                    $sellerinvoice->getStoreId()
                );
            }
            $sellerPage = $this->newPage();
            $sellerOrder = $sellerinvoice->getOrder();
            /* Add image */
            $this->insertLogo(
                $sellerPage,
                $sellerinvoice->getStore()
            );
            /* Add address */
            $this->insertAddress(
                $sellerPage,
                $sellerinvoice->getStore()
            );
            /* Add head */
            $this->insertOrder(
                $sellerPage,
                $sellerinvoice,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $sellerOrder->getStoreId()
                )
            );
            /* Add document text and number */
            $this->insertDocumentNumber($sellerPage, __('Invoice # ') . $sellerinvoice->getIncrementId());
            /* Add table */
            $this->_drawHeader($sellerPage);
            /* Add body */
            foreach ($sellerinvoice->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $sellerPage, $sellerOrder);
                $sellerPage = end($sellerPdf->pages);
            }
            /* Add totals */
            $this->insertTotals(
                $sellerPage,
                $sellerinvoice
            );
            if ($sellerinvoice->getStoreId()) {
                $this->_localeResolver->revert();
            }
        }
        $this->_afterGetPdf();
        return $sellerPdf;
    }

    /**
     * Insert order to seller's order pdf page
     *
     * @param \Zend_Pdf_Page &$sellerPdfPage
     * @param \Magento\Sales\Model\Order $sellerOrderObj
     * @param bool $putOrderId
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function insertOrder(&$sellerPdfPage, $sellerOrderObj, $putOrderId = true)
    {
        if ($sellerOrderObj instanceof \Magento\Sales\Model\Order) {
            $sellerShipment = null;
            $sellerInvoice = null;
            $sellerOrder = $sellerOrderObj;
        } elseif ($sellerOrderObj instanceof \Magento\Sales\Model\Order\Shipment) {
            $sellerInvoice = null;
            $sellerShipment = $sellerOrderObj;
            $sellerOrder = $sellerShipment->getOrder();
        } elseif ($sellerOrderObj instanceof \Magento\Sales\Model\Order\Invoice) {
            $sellerShipment = null;
            $sellerInvoice = $sellerOrderObj;
            $sellerOrder = $sellerInvoice->getOrder();
        }

        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;

        $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $sellerPdfPage->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $sellerPdfPage->drawRectangle(25, $top, 570, $top - 55);
        $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->setDocHeaderCoordinates([25, $top, 570, $top - 55]);
        $this->_setFontRegular($sellerPdfPage, 10);

        if ($putOrderId) {
            $sellerPdfPage->drawText(
                __('Order # ') . $sellerOrder->getRealOrderId(),
                35,
                $top -= 30,
                'UTF-8'
            );
        }
        $sellerPdfPage->drawText(
            __('Order Date: ') .
            $this->_localeDate->formatDate(
                $this->_localeDate->scopeDate(
                    $sellerOrder->getStore(),
                    $sellerOrder->getCreatedAt(),
                    true
                ),
                \IntlDateFormatter::MEDIUM,
                false
            ),
            35,
            $top -= 15,
            'UTF-8'
        );

        $top -= 10;
        $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $sellerPdfPage->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $sellerPdfPage->setLineWidth(0.5);
        $sellerPdfPage->drawRectangle(25, $top, 275, $top - 25);
        $sellerPdfPage->drawRectangle(275, $top, 570, $top - 25);

        if ($this->helper->getSellerProfileDisplayFlag()) {
            /* Calculate blocks info */
            $this->doInsertOrderExecution($sellerPdfPage, $sellerOrder, $sellerShipment, $top, $sellerInvoice);
        } else {
            /* Calculate blocks info */

            /* Billing Address */
            $billingAddress = $this->_formatAddress(
                $this->addressRenderer->format(
                    $sellerOrder->getBillingAddress(),
                    'pdf'
                )
            );

            /* Shipping Address and Method */
            if (!$sellerOrder->getIsVirtual()) {
                /* Shipping Address */
                $shippingAddress = $this->_formatAddress(
                    $this->addressRenderer->format($sellerOrder->getShippingAddress(), 'pdf')
                );
                $shippingMethod = $sellerOrder->getShippingDescription();
            }

            $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $this->_setFontBold($sellerPdfPage, 12);
            $sellerPdfPage->drawText(__('Payment Method:'), 35, $top - 15, 'UTF-8');
            if (!$sellerOrder->getIsVirtual()) {
                $sellerPdfPage->drawText(__('Shipping Method:'), 285, $top - 15, 'UTF-8');
            }

            $addressesHeight = $this->_calcAddressHeight($billingAddress);
            if (isset($shippingAddress)) {
                $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
            }

            $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
            if (!$sellerOrder->getIsVirtual()) {
                $sellerPdfPage->drawRectangle(25, $top - 25, 570, $top - 33 - $addressesHeight);
            } else {
                $sellerPdfPage->drawRectangle(25, $top - 25, 570, $top - 65);
            }
            $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $this->_setFontRegular($sellerPdfPage, 10);
            $this->y = $top - 40;
            $this->y -= 15;

            if (!$sellerOrder->getIsVirtual()) {
                $topMargin = 15;
                $methodStartY = $this->y;

                foreach ($this->string->split($shippingMethod, 45, true, true) as $_value) {
                    $sellerPdfPage->drawText(strip_tags(trim($_value)), 285, $this->y, 'UTF-8');
                    $this->y -= 15;
                }

                $yShipments = $this->y;
                $totalShippingChargesText = "(" . __(
                    'Total Shipping Charges'
                ) . " " . $sellerOrder->formatPriceTxt(
                    $sellerInvoice->getShippingAmount()
                ) . ")";

                $sellerPdfPage->drawText(
                    $totalShippingChargesText,
                    285,
                    $yShipments - $topMargin,
                    'UTF-8'
                );
                $yShipments -= $topMargin + 10;

                $tracks = [];
                if ($sellerShipment) {
                    $tracks = $sellerShipment->getAllTracks();
                }
                if (count($tracks)) {
                    $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                    $sellerPdfPage->setLineWidth(0.5);
                    $sellerPdfPage->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                    $sellerPdfPage->drawLine(400, $yShipments, 400, $yShipments - 10);

                    $this->_setFontRegular($sellerPdfPage, 9);
                    $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                    $sellerPdfPage->drawText(__('Title'), 290, $yShipments - 7, 'UTF-8');
                    $sellerPdfPage->drawText(__('Number'), 410, $yShipments - 7, 'UTF-8');

                    $yShipments -= 20;
                    $this->_setFontRegular($sellerPdfPage, 8);
                    foreach ($tracks as $track) {
                        $maxTitleLen = 45;
                        $endOfTitle = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
                        $truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
                        $sellerPdfPage->drawText($truncatedTitle, 292, $yShipments, 'UTF-8');
                        $sellerPdfPage->drawText($track->getNumber(), 410, $yShipments, 'UTF-8');
                        $yShipments -= $topMargin - 5;
                    }
                } else {
                    $yShipments -= $topMargin - 5;
                }

                $currentY = min($this->y, $yShipments);

                $this->y = $currentY;
                $this->y -= 15;
            } else {
                $this->y -= 55;
            }
        }
    }

    /**
     * Insert Seller logo to seller pdf page.
     *
     * @param \Zend_Pdf_Page &$sellerPdfPage
     * @param null           $store
     */
    protected function insertLogo(&$sellerPdfPage, $store = null)
    {
        $sellerImage = '';
        $sellerImageFlag = 0;
        $sellerId = $this->helper->getCustomerId();
        // get seller data for store in which order is placed
        $collection = $this->helper->getSellerCollectionObj($sellerId);
        foreach ($collection as $row) {
            $sellerImage = $row->getLogoPic();
            if ($sellerImage) {
                $sellerImageFlag = 1;
            }
        }

        if ($sellerImage == '') {
            $sellerImage = $this->_scopeConfig
            ->getValue(
                'sales/identity/logo',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            );
            $sellerImageFlag = 0;
        }
        $this->y = $this->y ? $this->y : 815;
        if ($sellerImage) {
            if ($sellerImageFlag == 0) {
                $sellerImagePath = '/sales/store/logo/'.$sellerImage;
            } else {
                $sellerImagePath = '/avatar/'.$sellerImage;
            }
            if ($this->_mediaDirectory->isFile($sellerImagePath)) {
                $sellerImage = \Zend_Pdf_Image::imageWithPath(
                    $this->_mediaDirectory->getAbsolutePath($sellerImagePath)
                );
                $imageTop = 830; //top border of the page
                $imageWidthLimit = 270; //image width half of the page width
                $imageHeightLimit = 270;
                $imageWidth = $sellerImage->getPixelWidth();
                $imageHeight = $sellerImage->getPixelHeight();

                //preserving seller image aspect ratio
                $imageRatio = $imageWidth / $imageHeight;
                if ($imageRatio > 1 && $imageWidth > $imageWidthLimit) {
                    $imageWidth = $imageWidthLimit;
                    $imageHeight = $imageWidth / $imageRatio;
                } elseif ($imageRatio < 1 && $imageHeight > $imageHeightLimit) {
                    $imageHeight = $imageHeightLimit;
                    $imageWidth = $imageHeight * $imageRatio;
                } elseif ($imageRatio == 1 && $imageHeight > $imageHeightLimit) {
                    $imageHeight = $imageHeightLimit;
                    $imageWidth = $imageWidthLimit;
                }
                $y1Axis = $imageTop - $imageHeight;
                $y2Axis = $imageTop;
                $x1Axis = 25;
                $x2Axis = $x1Axis + $imageWidth;
                //seller image coordinates after transformation seller image are rounded by Zend
                $sellerPdfPage->drawImage($sellerImage, $x1Axis, $y1Axis, $x2Axis, $y2Axis);
                $this->y = $y1Axis - 10;
            }
        }
    }

    /**
     * Insert seller address address and other info to pdf page.
     *
     * @param \Zend_Pdf_Page &$sellerPdfPage
     * @param null           $store
     */
    protected function insertAddress(&$sellerPdfPage, $store = null)
    {
        $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $font = $this->_setFontRegular($sellerPdfPage, 10);
        $sellerPdfPage->setLineWidth(0);
        $this->y = $this->y ? $this->y : 815;
        $imageTop = 815;

        $address = '';
        $sellerId = $this->helper->getCustomerId();
        // get seller data for store in which order is placed
        $collection = $this->helper->getSellerCollectionObj($sellerId);
        foreach ($collection as $row) {
            $address = $row->getOthersInfo();
        }

        if ($address == '') {
            $address = $this->_scopeConfig->getValue(
                'sales/identity/address',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            );
        }

        foreach (explode("\n", $address) as $value) {
            if ($value !== '') {
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $sellerPdfPage->drawText(
                        trim(strip_tags($_value)),
                        $this->getAlignRight($_value, 130, 440, $font, 10),
                        $imageTop,
                        'UTF-8'
                    );
                    $imageTop -= 10;
                }
            }
        }
        $this->y = $this->y > $imageTop ? $imageTop : $this->y;
    }

    protected function doInsertOrderExecution($sellerPdfPage, $sellerOrder, $sellerShipment, $top, $sellerInvoice)
    {
        /* Billing Address */
        $billingAddress = $this->_formatAddress(
            $this->addressRenderer->format(
                $sellerOrder->getBillingAddress(),
                'pdf'
            )
        );

        /* Payment */

        /* Shipping Address and Method */
        if (!$sellerOrder->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress(
                $this->addressRenderer->format($sellerOrder->getShippingAddress(), 'pdf')
            );
            $shippingMethod = $sellerOrder->getShippingDescription();
        }

        $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($sellerPdfPage, 12);
        $sellerPdfPage->drawText(__('Sold to:'), 35, $top - 15, 'UTF-8');

        if (!$sellerOrder->getIsVirtual()) {
            $sellerPdfPage->drawText(__('Ship to:'), 285, $top - 15, 'UTF-8');
        } else {
            $sellerPdfPage->drawText(__('Payment Method:'), 285, $top - 15, 'UTF-8');
        }

        $addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }

        $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $sellerPdfPage->drawRectangle(25, $top - 25, 570, $top - 33 - $addressesHeight);
        $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($sellerPdfPage, 10);
        $this->y = $top - 40;
        $addressesStartY = $this->y;

        foreach ($billingAddress as $value) {
            $sellerPdfPage = $this->calculateBillingYaxis($value, $sellerPdfPage);
        }

        $addressesEndY = $this->y;

        if (!$sellerOrder->getIsVirtual()) {
            $this->y = $addressesStartY;
            foreach ($shippingAddress as $value) {
                $sellerPdfPage = $this->calculateShippingYaxis($value, $sellerPdfPage);
            }

            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;

            $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $sellerPdfPage->setLineWidth(0.5);
            $sellerPdfPage->drawRectangle(25, $this->y, 275, $this->y - 25);
            $sellerPdfPage->drawRectangle(275, $this->y, 570, $this->y - 25);

            $this->y -= 15;
            $this->_setFontBold($sellerPdfPage, 12);
            $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $sellerPdfPage->drawText(__('Payment Method'), 35, $this->y, 'UTF-8');
            $sellerPdfPage->drawText(__('Shipping Method:'), 285, $this->y, 'UTF-8');

            $this->y -= 10;
            $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($sellerPdfPage, 10);
            $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments = $this->y - 15;
        } else {
            $yPayments = $addressesStartY;
            $paymentLeft = 285;
        }

        if ($sellerOrder->getIsVirtual()) {
            // replacement of Shipments-Payments rectangle block
            $yPayments = min($addressesEndY, $yPayments);
            $sellerPdfPage->drawLine(25, $top - 25, 25, $yPayments);
            $sellerPdfPage->drawLine(570, $top - 25, 570, $yPayments);
            $sellerPdfPage->drawLine(25, $yPayments, 570, $yPayments);

            $this->y = $yPayments - 15;
        } else {
            $topMargin = 15;
            $methodStartY = $this->y;
            $this->y -= 15;

            foreach ($this->string->split($shippingMethod, 45, true, true) as $_value) {
                $sellerPdfPage->drawText(strip_tags(trim($_value)), 285, $this->y, 'UTF-8');
                $this->y -= 15;
            }

            $yShipments = $this->y;
            $totalShippingChargesText = "(" . __(
                'Total Shipping Charges'
            ) . " " . $sellerOrder->formatPriceTxt(
                $sellerInvoice->getShippingAmount()
            ) . ")";

            $sellerPdfPage->drawText($totalShippingChargesText, 285, $yShipments - $topMargin, 'UTF-8');
            $yShipments -= $topMargin + 10;

            $tracks = [];
            if ($sellerShipment) {
                $tracks = $sellerShipment->getAllTracks();
            }
            if (count($tracks)) {
                $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $sellerPdfPage->setLineWidth(0.5);
                $sellerPdfPage->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                $sellerPdfPage->drawLine(400, $yShipments, 400, $yShipments - 10);

                $this->_setFontRegular($sellerPdfPage, 9);
                $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $sellerPdfPage->drawText(__('Title'), 290, $yShipments - 7, 'UTF-8');
                $sellerPdfPage->drawText(__('Number'), 410, $yShipments - 7, 'UTF-8');

                $yShipments -= 20;
                $this->_setFontRegular($sellerPdfPage, 8);
                foreach ($tracks as $track) {
                    $maxTitleLen = 45;
                    $endOfTitle = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
                    $truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
                    $sellerPdfPage->drawText($truncatedTitle, 292, $yShipments, 'UTF-8');
                    $sellerPdfPage->drawText($track->getNumber(), 410, $yShipments, 'UTF-8');
                    $yShipments -= $topMargin - 5;
                }
            } else {
                $yShipments -= $topMargin - 5;
            }

            $currentY = min($yPayments, $yShipments);

            // replacement of Shipments-Payments rectangle block
            $sellerPdfPage->drawLine(25, $methodStartY, 25, $currentY);
            //left
            $sellerPdfPage->drawLine(25, $currentY, 570, $currentY);
            //bottom
            $sellerPdfPage->drawLine(570, $currentY, 570, $methodStartY);
            //right

            $this->y = $currentY;
            $this->y -= 15;
        }
    }

    protected function calculateBillingYaxis($value, $sellerPdfPage)
    {
        if ($value !== '') {
            $text = [];
            foreach ($this->string->split($value, 45, true, true) as $_value) {
                $text[] = $_value;
            }
            foreach ($text as $part) {
                $sellerPdfPage->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                $this->y -= 15;
            }
        }
        return $sellerPdfPage;
    }

    protected function calculateShippingYaxis($value, $sellerPdfPage)
    {
        if ($value !== '') {
            $text = [];
            foreach ($this->string->split($value, 45, true, true) as $_value) {
                $text[] = $_value;
            }
            foreach ($text as $part) {
                $sellerPdfPage->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
                $this->y -= 15;
            }
        }
        return $sellerPdfPage;
    }
}
