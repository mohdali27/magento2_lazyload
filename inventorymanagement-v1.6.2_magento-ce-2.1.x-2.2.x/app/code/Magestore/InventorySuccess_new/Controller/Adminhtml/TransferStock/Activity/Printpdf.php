<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\Activity;

use Magestore\InventorySuccess\Api\Data\TransferStock\TransferActivityInterface;
use Magento\Framework\App\Filesystem\DirectoryList;


class Printpdf extends \Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\AbstractRequest
{
    /**
     * @var \Magestore\InventorySuccess\Model\TransferStock\TransferActivityFactory
     */
    protected $_transferActivityFactory;

    protected $csvProcessor;
    protected $fileFactory;
    protected $filesystem;
    protected $_rootDirectory;

    /** @var  \Magestore\InventorySuccess\Model\Locator\LocatorFactory $_locatorFactory */
    protected $_locatorFactory;

    /** @var \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferActivityProduct\CollectionFactory  $_collection */
    protected $_collection;

    protected $y;
    protected $_pdf;
    protected $string;

    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context,
        \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferActivityProduct\CollectionFactory  $_collection,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Stdlib\StringUtils $string
    ){
        parent::__construct($context);
        $this->_transferActivityFactory = $context->getTransferActivityFactory();
        $this->_collection = $_collection;
        $this->csvProcessor = $csvProcessor;
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
        $this->_rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->string = $string;
    }

    public function widthForStringUsingFontSize($string, $font, $fontSize)
    {
        $drawingString = '"libiconv"' == ICONV_IMPL ? iconv(
            'UTF-8',
            'UTF-16BE//IGNORE',
            $string
        ) : @iconv(
            'UTF-8',
            'UTF-16BE',
            $string
        );

        $characters = [];
        for ($i = 0; $i < strlen($drawingString); $i++) {
            $characters[] = ord($drawingString[$i++]) << 8 | ord($drawingString[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = array_sum($widths) / $font->getUnitsPerEm() * $fontSize;
        return $stringWidth;
    }


    public function drawLineBlocks(\Zend_Pdf_Page $page, array $draw, array $pageSettings = [])
    {
        foreach ($draw as $itemsProp) {
            if (!isset($itemsProp['lines']) || !is_array($itemsProp['lines'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('We don\'t recognize the draw line data. Please define the "lines" array.')
                );
            }
            $lines = $itemsProp['lines'];
            $height = isset($itemsProp['height']) ? $itemsProp['height'] : 10;

            if (empty($itemsProp['shift'])) {
                $shift = 0;
                foreach ($lines as $line) {
                    $maxHeight = 0;
                    foreach ($line as $column) {
                        $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                        if (!is_array($column['text'])) {
                            $column['text'] = [$column['text']];
                        }
                        $top = 0;
                        foreach ($column['text'] as $part) {
                            $top += $lineSpacing;
                        }

                        $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                    }
                    $shift += $maxHeight;
                }
                $itemsProp['shift'] = $shift;
            }

            if ($this->y - $itemsProp['shift'] < 15) {
                $page = $this->newPage($pageSettings);
            }

            foreach ($lines as $line) {
                $maxHeight = 0;
                foreach ($line as $column) {
                    $fontSize = empty($column['font_size']) ? 10 : $column['font_size'];
                    if (!empty($column['font_file'])) {
                        $font = \Zend_Pdf_Font::fontWithPath($column['font_file']);
                        $page->setFont($font, $fontSize);
                    } else {
                        $fontStyle = empty($column['font']) ? 'regular' : $column['font'];
                        switch ($fontStyle) {
                            case 'bold':
                                $font = $this->_setFontBold($page, $fontSize);
                                break;
                            case 'italic':
                                $font = $this->_setFontItalic($page, $fontSize);
                                break;
                            default:
                                $font = $this->_setFontRegular($page, $fontSize);
                                break;
                        }
                    }

                    if (!is_array($column['text'])) {
                        $column['text'] = [$column['text']];
                    }
                    $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                    $top = 0;
                    foreach ($column['text'] as $part) {
                        if ($this->y - $lineSpacing < 15) {
                            $page = $this->newPage($pageSettings);
                        }
                        $feed = $column['feed'];
                        $textAlign = empty($column['align']) ? 'left' : $column['align'];
                        $width = empty($column['width']) ? 0 : $column['width'];
                        switch ($textAlign) {
                            case 'right':
                                if ($width) {
                                    $feed = $this->getAlignRight($part, $feed, $width, $font, $fontSize);
                                } else {
                                    $feed = $feed - $this->widthForStringUsingFontSize($part, $font, $fontSize);
                                }
                                break;
                            case 'center':
                                if ($width) {
                                    $feed = $this->getAlignCenter($part, $feed, $width, $font, $fontSize);
                                }
                                break;
                            default:
                                break;
                        }
                        $page->drawText($part, $feed, $this->y - $top, 'UTF-8');
                        $top += $lineSpacing;
                    }

                    $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                }
                $this->y -= $maxHeight;
            }
        }
        return $page;
    }

    /**
     * Create new page and assign to PDF object
     *
     * @param  array $settings
     * @return \Zend_Pdf_Page
     */
    public function newPage(array $settings = [])
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(\Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;
        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }
        return $page;
    }

    /**
     * @return \Zend_Pdf
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getPdf()
    {
        if (!$this->_pdf instanceof \Zend_Pdf) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please define the PDF object before using.'));
        }
        return $this->_pdf;
    }

    /**
     * Draw table header for product items
     *
     * @param  \Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        //columns headers
        $lines[0][] = ['text' => __('Qty'), 'feed' => 35];

        $lines[0][] = ['text' => __('Products'), 'feed' => 100];

        $lines[0][] = ['text' => __('SKU'), 'feed' => 565, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 10];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    /**
     * Set font as regular
     *
     * @param  \Zend_Pdf_Page $object
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontRegular($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Re-4.4.1.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontBold($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set PDF object
     *
     * @param  \Zend_Pdf $pdf
     * @return $this
     */
    protected function _setPdf(\Zend_Pdf $pdf)
    {
        $this->_pdf = $pdf;
        return $this;
    }


    /**
     * return pdf file
     */
    public function execute()
    {
        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        $page = $this->newPage();

        /* add header */
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->drawRectangle(25, $this->y, 570, $this->y - 75);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->_setFontRegular($page, 10);

        /* draw header text */
        $this->drawTextHeader($page);

        /*add header text of table item*/
        $this->y -= 50;
        $this->_drawHeader($page);

        /* add item */
        $items = $this->generateSampleData();
        foreach($items as $item){
            $varienObject = new \Magento\Framework\DataObject();
            $varienObject->setData($item);

            /* Draw item */
            $this->_drawItem($varienObject, $page, $pdf);
            $page = end($pdf->pages);
        }
        $pdfData = $pdf->render();
        $filename = $this->getFileName();
        return $this->fileFactory->create(
            sprintf($filename, ''),
            $pdfData,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }

    /**
     * @return array
     */
    public function generateSampleData() {
        $id = $this->_request->getParam("id");
        $data = array();
        $transferActivityProduct = $this->_collection->create();
        if($id){
            $transferActivityProduct->addFieldToFilter('activity_id',$id);
        }
        foreach ($transferActivityProduct as $product) {
                $data[]= array(
                    'name' => $product->getData('product_name'),
                    'sku' => $product->getData('product_sku'),
                    'qty' => $product->getData('qty'),
                );
        }
        return $data;
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @param \Zend_Pdf_Page $page
     * @param $pdf
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _drawItem(\Magento\Framework\DataObject $item, \Zend_Pdf_Page $page , $pdf)
    {
        $lines = [];
        // draw Product name
        $lines[0] = [['text' => $this->string->split($item->getName(), 60, true, true), 'feed' => 100]];
        // draw QTY
        $lines[0][] = ['text' => $item->getQty() * 1, 'feed' => 35];
        // draw SKU
        $lines[0][] = [
            'text' => $this->string->split($item->getSku(), 25),
            'feed' => 565,
            'align' => 'right',
        ];
        $lineBlock = ['lines' => $lines, 'height' => 20];
        $page = $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);

    }

    /**
     * @return string
     */
    public function getFileName(){
        $id = $this->_request->getParam("id");
        /** @var \Magestore\InventorySuccess\Model\Locator\Locator $locator */
        $transferActivity = $this->_transferActivityFactory->create();
        if($id){
            $transferActivity->load($id);
        }
        $activity_type = $transferActivity->getActivityType();
        if($activity_type == TransferActivityInterface::ACTIVITY_TYPE_RETURN){
            $name = 'returned_list.pdf';
        }elseif($activity_type == TransferActivityInterface::ACTIVITY_TYPE_RECEIVING){
            $name = 'received_list.pdf';
        }elseif($activity_type == TransferActivityInterface::ACTIVITY_TYPE_DELIVERY){
            $name = 'delivered_list.pdf';
        }else{
            $name = 'products_list.pdf';
        }
        return $name;
    }

    /**
     * @param $page
     * @return mixed
     */
    public function drawTextHeader($page){
        $id = $this->_request->getParam("id");
        /** @var \Magestore\InventorySuccess\Model\Locator\Locator $locator */
        $transferActivity = $this->_transferActivityFactory->create();
        if($id){
            $transferActivity->load($id);
        }
        $activity_type = $transferActivity->getActivityType();
        if($activity_type == TransferActivityInterface::ACTIVITY_TYPE_RETURN){
            $page->drawText(__('Return Id # ') . $id, 35, $this->y -= 15, 'UTF-8');
        }elseif($activity_type == TransferActivityInterface::ACTIVITY_TYPE_RECEIVING){
            $page->drawText(__('Received Id # ') . $id, 35, $this->y -= 15, 'UTF-8');
        }elseif($activity_type == TransferActivityInterface::ACTIVITY_TYPE_DELIVERY){
            $page->drawText(__('Delivery Id # ') . $id, 35, $this->y -= 15, 'UTF-8');
        }

        $page->drawText(__('Created At : ') . $transferActivity->getCreatedAt(), 35, $this->y -= 10, 'UTF-8');
        $page->drawText(__('Created By : ') . $transferActivity->getCreatedBy(), 35, $this->y -= 10, 'UTF-8');
        $page->drawText(__('Note :  ') . $transferActivity->getNote(), 35, $this->y -= 10, 'UTF-8');
        return $page;
    }


    /* sample function - not to do anything */
    public function draw()
    {
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();
        $lines = [];
        // draw Product name
        $lines[0] = [['text' => $this->string->split($item->getName(), 60, true, true), 'feed' => 100]];
        // draw QTY
        $lines[0][] = ['text' => $item->getQty() * 1, 'feed' => 35];

        // draw SKU
        $lines[0][] = [
            'text' => $this->string->split($item->getSku(), 25),
            'feed' => 565,
            'align' => 'right',
        ];
        // Custom options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = [
                    'text' => $this->string->split($this->filterManager->stripTags($option['label']), 70, true, true),
                    'font' => 'italic',
                    'feed' => 110,
                ];

                // draw options value
                if ($option['value']) {
                    $printValue = isset(
                        $option['print_value']
                    ) ? $option['print_value'] : $this->filterManager->stripTags(
                        $option['value']
                    );
                    $values = explode(', ', $printValue);
                    foreach ($values as $value) {
                        $lines[][] = ['text' => $this->string->split($value, 50, true, true), 'feed' => 115];
                    }
                }
            }
        }
        $lineBlock = ['lines' => $lines, 'height' => 20];
        $page = $pdf->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $this->setPage($page);
    }

}


