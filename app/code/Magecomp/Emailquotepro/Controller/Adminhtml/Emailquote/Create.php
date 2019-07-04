<?php

namespace Magecomp\Emailquotepro\Controller\Adminhtml\Emailquote;

use Magecomp\Emailquotepro\Helper\Data as EamilQuoteHelper;
use Magecomp\Emailquotepro\Model\EmailproductquoteFactory;
use Magecomp\Emailquotepro\Model\Mail\TransportBuilder;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Backend\Model\Session\Quote as BackendModelSession;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Header\Logo;

class Create extends Action
{
    const XML_PATH_EMAIL_ADMIN_QUOTE_SENDER = 'emailquote/adminsettings/customeremailsender';
    const XML_PATH_EMAIL_CUSTOMER_QUOTE_NOTIFICATION = 'emailquote/adminsettings/customertemplate';
    const XML_PATH_EMAIL_ADMIN_NAME = 'Admin';
    const XML_PATH_EMAIL_ADMIN_EMAIL = 'emailquote/general/adminmailreceiver';
    protected $_EmailproductquoteFactory;
    protected $inlineTranslation;
    protected $transportBuilder;
    protected $scopeConfig;
    protected $_productloader;
    protected $_priceHelper;
    protected $_mailcartHelper;
    protected $_modelStoreManagerInterface;
    protected $_logo;

    public function __construct(
        Context $context,
        EmailproductquoteFactory $EmailproductquoteFactory,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $configScopeConfigInterface,
        ProductFactory $_productloader,
        PricingHelper $priceHelper,
        EamilQuoteHelper $mailcartHelper,
        BackendModelSession $backendModelSession,
        StoreManagerInterface $storeManagerInterface,
        Filesystem $filesystem,
        StringUtils $string,
        Logo $logo
    )
    {
        $this->_EmailproductquoteFactory = $EmailproductquoteFactory;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $configScopeConfigInterface;
        $this->transportBuilder = $transportBuilder;
        $this->_productloader = $_productloader;
        $this->_priceHelper = $priceHelper;
        $this->_mailcartHelper = $mailcartHelper;
        $this->backendModelSession = $backendModelSession;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->string = $string;
        $this->_logo = $logo;
        parent::__construct($context);
    }

    public function execute()
    {
		
$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("test log");
        $redirectPath = "sales/order_create/";
        try {
            $quoteData = $this->backendModelSession->getQuote();
            if ($quoteData->getEntityId() > 0) {
                $pId = array();
                $pSKU = array();
                foreach ($quoteData->getAllItems() as $item) {
                    $pId[] = $item->getProduct()->getId();
                    $pSKU[] = $item->getProduct()->getSku();
                }

                $customerComment = $quoteData->getCustomerNote();
                if (implode(",", $pId) != '') {
                    $modelEmailProduct = $this->_EmailproductquoteFactory->create();
                    $modelEmailProduct->load($quoteData->getEntityId(), 'quote_id');
					$customerName = "";
					$customerName = $quoteData->getCustomerFirstname() . ' ' . $quoteData->getCustomerLastname();
                    $modelEmailProduct->setQuoteId($quoteData->getEntityId())
                        ->setProductId(implode(",", $pId))
                        ->setProductSku(implode(",", $pSKU))
                        ->setCustomerEmail($quoteData->getCustomerEmail())
                        ->setCustomerName($customerName)
                        ->setGrandTotal($quoteData->getSubtotal())
                        ->setStatus(0)
                        ->save();

                    /* CREATE QUOTE HTML (START) */
                    $qhtml = "<tr style='background-color:#e0e0e0'>";
                    $qhtml .= "<th>Photo</th><th>Item</th><th>SKU</th><th>Qty</th><th class='right'>Total</th>";
                    $qhtml .= "</tr>";

                    $quoteId = $quoteData->getEntityId();
                    foreach ($quoteData->getAllVisibleItems() as $item) {
                        $product = $this->_productloader->create()->load($item->getProductId());
                        $store = $this->storeManagerInterface->getStore();
                        if ($product->getImage() == "") {
                            $img = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product/placeholder/' . $this->_mailcartHelper->getConfig('catalog/placeholder/thumbnail_placeholder');
                        } else {
                            $img = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
                        }
                        $qhtml .= "<tr>";
                        $qhtml .= "<td style='text-align:center'><img src=" . $img . " alt=" . $item->getName() . " width='100' height='100' /></td>";
                        $qhtml .= "<td style='text-align:center'>" . $item->getName();

                        /* Bundle Product Option  start*/
                        $products = $item->getProduct();
                        if ($products->getTypeId() === 'bundle') {
                            $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                            foreach ($options['bundle_options'] as $optionssub):
                                $qhtml .= "<br /><strong style='font-size:12px;'>";
                                $qhtml .= $optionssub['label'];
                                $qhtml .= "</strong>";
                                foreach ($optionssub['value'] as $selection) {
                                    $formattedPriceOptions = $this->_priceHelper->currency($selection['price'], true, false);
                                    $qhtml .= $selection['qty'] . " x " . $selection['title'] . " " . $formattedPriceOptions;
                                }
                            endforeach;
                        }
                        /* Bundle Product Option  end*/
                        $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                        if (array_key_exists("attributes_info", $options) && in_array("attributes_info", $options)) :
                            foreach ($options['attributes_info'] as $curopt) {
                                $qhtml .= "<br /><strong style='font-size:12px;'>";
                                $qhtml .= $curopt['label'] . " : " . $curopt['value'];
                                $qhtml .= "</strong>";
                            }
                        endif;
                        $qhtml .= "</td>";
                        $qhtml .= "<td style='text-align:center'>" . $item->getSku() . "</td>";
                        $qhtml .= "<td style='text-align:center'>" . $item->getQty() . "</td>";
                        $qhtml .= "<td style='text-align:center'>" . $this->_priceHelper->currency($item->getRowTotalInclTax(), true, false) . "</td>";
                        $qhtml .= "</tr>";
                    }
                    $address = $quoteData->getShippingAddress();
                    $shippingrate = $address->getBaseShippingAmount();
                    $taxrate = $address->getData('tax_amount');

                    $formateShippingrate = $this->_priceHelper->currency($shippingrate, true, false);
                    $formateTaxrate = $this->_priceHelper->currency($taxrate, true, false);

                    $totals = $quoteData->getTotals(); //Total object
                    $grandtotal = $totals["grand_total"]->getValue(); //Grandtotal value
                    $formattedPrice = $this->_priceHelper->currency($grandtotal, true, false);


                    if ($quoteData->getCouponCode()) {
                        $coupon_code = $quoteData->getCouponCode(); //Total object
                        $totalDiscount = $quoteData->getBaseSubtotal() - $quoteData->getSubtotalWithDiscount();
                        $formattedTotalDiscount = $this->_priceHelper->currency($totalDiscount, true, false);
                        $qhtml .= "<tr>";
                        $qhtml .= "<td valign='top' colspan='5'>";
                        $qhtml .= "<p style='border:1px solid #E0E0E0; font-size:12px; line-height:16px; margin:0; padding:13px 18px; background:#F9F9F9; text-align:right;'><strong>Coupon Code (" . $coupon_code . "): " . $formattedTotalDiscount . "</strong></p></td>";
                        $qhtml .= "</tr>";
                    }

                    if ($shippingrate > 0) {
                        $qhtml .= "<tr>";
                        $qhtml .= "<td valign='top' colspan='5'>";
                        $qhtml .= "<p style='border:1px solid #E0E0E0; font-size:12px; line-height:16px; margin:0; padding:13px 18px; background:#F9F9F9; text-align:right;'><strong>" . __('Shipping Price') . " : " . $formateShippingrate . "</strong></p></td>";
                        $qhtml .= "</tr>";
                    }
                    if ($formateTaxrate > 0) {
                        $qhtml .= "<tr>";
                        $qhtml .= "<td valign='top' colspan='5'>";
                        $qhtml .= "<p style='border:1px solid #E0E0E0; font-size:12px; line-height:16px; margin:0; padding:13px 18px; background:#F9F9F9; text-align:right;'><strong>" . __('Tax Rate') . " : " . $formateTaxrate . "</strong></p></td>";
                        $qhtml .= "</tr>";
                    }
                    $qhtml .= "<tr>";
                    $qhtml .= "<td valign='top' colspan='5'>";
                    $qhtml .= "<p style='border:1px solid #E0E0E0; font-size:12px; line-height:16px; margin:0; padding:13px 18px; background:#F9F9F9; text-align:right;'><strong>" . __('Grand Total') . ":" . $formattedPrice . "</strong></p></td>";
                    $qhtml .= "</tr>";
                    /* CREATE QUOTE HTML (END) */

                    $street = $address->getStreet()[0];
                    $city = $address->getCity();
                    $region = $address->getRegion();
                    $postcode = $address->getPostcode();

                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $countryName = $objectManager->create('\Magento\Directory\Model\Country')->load($address->getCountry())->getName();

                    /* Pdf Attachement cod start*/
                    $pdfContent = "";
                    if ($this->_mailcartHelper->isPDFEnable()) {

                        $pdfContent = $this->createPdf($totals, $quoteData->getAllVisibleItems(), $quoteData->getCustomerFirstname() . ' ' . $quoteData->getCustomerLastname(), $quoteData->getCustomerEmail(), $quoteData, $shippingrate, $taxrate, $customerComment);
                    }
                    /* Pdf Attachement code end*/
                    // Send Mail To Admin For This
                    $storeScope = ScopeInterface::SCOPE_STORE;
                    $this->inlineTranslation->suspend();
                    $template = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_CUSTOMER_QUOTE_NOTIFICATION, $storeScope);
                    $newQuoteId = $this->_mailcartHelper->generateRandomString() . $quoteData->getEntityId() . $this->_mailcartHelper->generateRandomString();

        $logger->info("before send mail : " . $template);
                    $transport = $this->transportBuilder
                        ->setTemplateIdentifier($template)
                        ->setTemplateOptions(
                            [
                                'area' => FrontNameResolver::AREA_CODE,
                                'store' => Store::DEFAULT_STORE_ID,
                            ]
                        )
                        ->setTemplateVars([
                            'customerName' => $quoteData->getCustomerFirstname(),
                            'customerEmail' => $quoteData->getCustomerEmail(),
                            'encryptquoteid' => $newQuoteId,
                            'quoteid' => $quoteId,
                            'orderComment' => $customerComment,
                            'street' => $street,
                            'city' => $city,
                            'region' => $region,
                            'country' => $countryName,
                            'postcode' => $postcode,
                            'cartgrid' => $qhtml
                        ])
                        ->setFrom($this->scopeConfig->getValue(self::XML_PATH_EMAIL_ADMIN_QUOTE_SENDER))
                        ->addTo($quoteData->getCustomerEmail())
                        ->addAttachment($pdfContent, $this->_mailcartHelper->isPDFEnable())
                        ->getTransport();
                    $transport->sendMessage();
                    $this->inlineTranslation->resume();
        $logger->info("before send after : " . $this->scopeConfig->getValue(self::XML_PATH_EMAIL_ADMIN_QUOTE_SENDER));					

        $logger->info("before send after : " . $quoteData->getCustomerEmail());					
                    $this->messageManager->addSuccess(__('Your Quote has been Sent Successfully'));
                } else {
                    $this->messageManager->addError(__('Please select some products, we will process it soon.'));
                    $redirectPath = "sales/order_create/";
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($redirectPath);
        return $resultRedirect;
    }

    /**
     * @param $totals
     * @param $items
     * @param $customername
     * @param $customeremail
     * @param $quote
     * @param $shippingrate
     * @param $taxrate
     * @param $customerComment
     * @return string
     */
    public function createPdf( $totals, $items, $customername, $customeremail, $quote, $shippingrate, $taxrate, $customerComment )
    {
        try {

            $pdf = new \Zend_Pdf(); //Create new PDF file
            $page = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
            $pdf->pages[] = $page;
            $top = 810;
            $left = 20;
            $this->getLogo($page);
            //Set Font

            $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA), 12);


            $page->drawText("Quote : #" . $quote->getId(), $left + 400, $top, 'UTF-8');
            $page->drawText("Date  : " . date("d/m/Y", strtotime($quote->getCreatedAt())), $left + 400, $top - 20, 'UTF-8');
            $page->drawText($customeremail, $left + 400, $top - 40, 'UTF-8');

            $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);  //Set Font
            $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA), 18);

            $top = $top - 80;

            $address = $quote->getShippingAddress();

            $page->drawText("ESTIMATE", $left + 220, $top - 50, 'UTF-8');

            $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA), 14);  //Set Font
            $page->drawText(ucwords($customername), $left, $top - 30, 'UTF-8');

            $topAddPlus = 50;
            if ($address->getStreet()[0] != "") {
                $page->drawText($address->getStreet()[0], $left, $top - $topAddPlus, 'UTF-8');
                $topAddPlus = 70;
            }
            if ($address->getCity() != "") {
                $page->drawText(ucwords($address->getCity()) . ", " . $address->getRegion(), $left, $top - $topAddPlus, 'UTF-8');
                $topAddPlus = $topAddPlus + 20;
            }
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $countryName = $objectManager->create('\Magento\Directory\Model\Country')->load($address->getCountry())->getName();
            if ($countryName != "") {
                $page->drawText($countryName, $left, $top - $topAddPlus, 'UTF-8');
                $topAddPlus = $topAddPlus + 20;
            }
            $page->drawText($address->getPostcode(), $left, $top - $topAddPlus, 'UTF-8');

            $top = $top - 310;
            $topstart = $top;
            $leftStart = 40;
            $addHeight = 25;

            $page->drawLine(25, $topstart + 170, 570, $topstart + 170);
            $page->drawText("  ", $leftStart, $topstart + 150, 'UTF-8');
            $page->drawText("Item  ", $leftStart + 130, $topstart + 150, 'UTF-8');
            $page->drawText("Qty  ", $leftStart + 280, $topstart + 150, 'UTF-8');
            $page->drawText("Unit Price", $leftStart + 345, $topstart + 150, 'UTF-8');
            $page->drawText("Total  ", $leftStart + 450, $topstart + 150, 'UTF-8');
            $page->drawLine(25, $topstart + 140, 570, $topstart + 140);

            $topstart = $topstart + 130;
            foreach ($items as $item) {
                if ($topstart < 100) {
                    $page = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
                    $pdf->pages[] = $page;
                    $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);
                    $topstart = 815;
                }
                $image = $this->getProductImage($item);
                $imageheight = 10;
                if ($image) {
                    $imageheight = 40;
                    $page->drawImage($image, $leftStart - 10, $topstart - 50, $leftStart + 60, $topstart + 5);
                }
                $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA), 12);
                $arrName = $this->string->split($item->getName(), 20);
                foreach ($arrName as $name) {
                    $topstart = $topstart - 15;
                    $page->drawText($name, $leftStart + 110, $topstart, 'UTF-8');
                }
                $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA), 11);
                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                $totalOptionHeight = 0;
                if (array_key_exists("attributes_info", $options) && count($options['attributes_info']) >= 1) :
                    $optionheight = 15;
                    $optionStart = $topstart - 15;
                    foreach ($options['attributes_info'] as $curopt) {
                        $page->drawText($curopt['label'] . ":" . $curopt['value'], $leftStart + 115, $optionStart - $optionheight, 'UTF-8');
                        $optionStart = $optionStart + $optionheight;
                        $totalOptionHeight += $optionheight;
                    }
                endif;
                $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA), 12);  //Set Font
                $topstart = $topstart + 20;

                $page->drawText($item->getQty(), $leftStart + 280, $topstart - 20, 'UTF-8');
                $page->drawText($this->_priceHelper->currency($item->getPrice(), true, false), $leftStart + 345, $topstart - 20, 'UTF-8');
                $page->drawText($this->_priceHelper->currency($item->getRowTotalInclTax(), true, false), $leftStart + 450, $topstart - 20, 'UTF-8');
                $topstart = $topstart - $addHeight - $totalOptionHeight - $imageheight;

            }
            if ($topstart < 100) {
                $page = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
                $pdf->pages[] = $page;
                $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);  //Set Font
                $topstart = 815;
            }
            $finaltotaltop = $topstart - 15;

            $page->drawLine(25, $finaltotaltop + 20, 570, $finaltotaltop + 20);
            $page->drawText("Shipping    : " . $this->_priceHelper->currency($shippingrate, true, false), $leftStart + 380, $finaltotaltop, 'UTF-8');

            $finaltotaltop = $finaltotaltop - 20;

            $page->drawText("Tax Rate    : " . $this->_priceHelper->currency($taxrate, true, false), $leftStart + 380, $finaltotaltop, 'UTF-8');

            $finaltotaltop = $finaltotaltop - 20;

            $page->drawText("Grand Total   : " . $this->_priceHelper->currency($totals["grand_total"]->getValue(), true, false), $leftStart + 370, $finaltotaltop, 'UTF-8');
            $page->drawLine(25, $finaltotaltop - 20, 570, $finaltotaltop - 20);


            $commentTop = $finaltotaltop - 40;
            $customerCommentArray = $this->string->split($customerComment, 100);
            $page->drawText("Comment : ", $leftStart - 5, $commentTop, 'UTF-8');
            $commentTop -= 10;
            foreach ($customerCommentArray as $name) {
                $commentTop = $commentTop - 15;
                $page->drawText($name, $leftStart - 5, $commentTop, 'UTF-8');
            }
            $footertext = 30;
            // footer
            $page->drawText($this->_mailcartHelper->getPDFFooterText(), 150, $footertext - 17, 'UTF-8');
            $pdfData = $pdf->render(); // Get PDF document as a string

            header("Content-Disposition: inline; filename=result.pdf");
            header("Content-type: application/x-pdf");

            return $pdfData;
        } catch (\Exception $e) {

        }
    }

    public function getLogo( $page )
    {
        $image = $this->scopeConfig->getValue(
            'design/header/logo_src',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManagerInterface->getStore()->getId()
        );
        $imagePath = '/logo/' . $image;
        if ($this->_mediaDirectory->isFile($imagePath)) {
            $image = \Zend_Pdf_Image::imageWithPath($this->_mediaDirectory->getAbsolutePath($imagePath));
            $top = 830;
            $width = $image->getPixelWidth();
            $height = $image->getPixelHeight();
            $y1 = $top - $height;
            $y2 = $top;
            $x1 = 25;
            $x2 = $x1 + $width;
            $page->drawImage($image, $x1, $y1, $x2, $y2);
        }
        return $page;
    }

    public function getProductImage( $item )
    {
        $productId = $item->getProduct()->getId();

        $image = $this->_productloader->create()->load($productId);
        if (!is_null($image)) {
            try {
                $imagePath = '/catalog/product/' . $image->getSmallImage();
                if ($this->_mediaDirectory->isFile($imagePath)) {
                    $image = \Zend_Pdf_Image::imageWithPath($this->_mediaDirectory->getAbsolutePath($imagePath));
                    return $image;
                } else {
                    return false;
                }
            } catch (\Exception $e) {
                return false;
            }
        }
        return false;
    }
    /*PDF code End*/
}