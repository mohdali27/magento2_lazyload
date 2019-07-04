<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Export\Adapter;

class Xml extends \Amasty\Feed\Model\Export\Adapter\Csv
{
    /**
     * Index of result array that consists of strings matched by the first parenthesized subpattern
     * @see http://php.net/manual/function.preg-match-all.php
     */
    const PREG_FIRST_SUBMASK= 1;

    protected $_fileHandler;
    protected $_header;
    protected $_item;
    protected $_content;
    protected $_contentAttributes;
    protected $_footer;

    private $tagsToRemove = [
        "g:additional_image_link",
        "g:sale_price_effective_date"
    ];

    /**
     * MIME-type for 'Content-Type' header.
     *
     * @return string
     */
    public function getContentType()
    {
        return 'text/xml';
    }

    /**
     * Return file extension for downloading.
     *
     * @return string
     */
    public function getFileExtension()
    {
        return 'xml';
    }


    /**
     * Write header
     *
     * @return $this
     */
    public function writeHeader()
    {
        if (!empty($this->_header)) {
            $header = str_replace(
                '<created_at>{{DATE}}</created_at>',
                '<created_at>' . date('Y-m-d H:i') . '</created_at>',
                $this->_header
            );
            $this->_fileHandler->write($header);
        }

        return $this;
    }

    /**
     * Write footer
     *
     * @return $this
     */
    public function writeFooter()
    {
        if (!empty($this->_footer)) {
            $this->_fileHandler->write($this->_footer);
        }

        return $this;
    }

    /**
     * Write row data to source file.
     *
     * @param array $rowData
     * @throws \Exception
     * @return $this
     */
    public function writeDataRow(array &$rowData)
    {
        $replace = [];
        if (is_array($this->_contentAttributes)) {
            foreach ($this->_contentAttributes as $search => $attribute) {

                $code = array_key_exists('parent', $attribute) && $attribute['parent'] == 'yes' ?
                    $attribute['attribute'] . '|parent' :
                    $attribute['attribute'];

                $value = $this->_modifyValue($attribute, isset($rowData[$code]) ? $rowData[$code] : '');
                $value = $this->_formatValue($attribute, $value);

                $replace['{' . $search . '}'] = $value;
            }
        }

        $write = '';

        if ($this->_item) {
            $write .= '<' . $this->_item . '>';
        }

        $writeItem = strtr($this->_content, $replace);

        foreach ($this->tagsToRemove as $tag) {
            $this->clearEmptyTag($writeItem, $tag);
        }

        $write .= $writeItem;

        if ($this->_item) {
            $write .= '</' . $this->_item . '>';
        }

        $this->_fileHandler->write($write);

        return $this;
    }

    /**
     * Modify value in field
     *
     * @param array $field
     * @param mixed $value
     * @return string
     */
    protected function _modifyValue($field, $value)
    {
        if ($field['modify'] != '') {
            foreach (explode('|', $field['modify']) as $modify) {
                $modifyArr = explode(":", $modify, 2);

                $modifyType = $modifyArr[0];
                $arg0 = null;
                $arg1 = null;

                if (isset($modifyArr[1])) {
                    $modifyArgs = explode("^", $modifyArr[1]);
                    if (isset($modifyArgs[0])) {
                        $arg0 = $modifyArgs[0];
                    }

                    if (isset($modifyArgs[1])) {
                        $arg1 = $modifyArgs[1];
                    }
                }

                $value = $this->_modify($value, $modifyType, $arg0, $arg1);
            }
        }

        return $value;
    }

    /**
     * Add CDATA
     *
     * @param array $field
     * @param mixed $value
     * @return string
     */
    protected function _formatValue($field, $value)
    {
        $ret = parent::_formatValue($field, $value);

        if (!empty($field['modify']) && !empty($ret) && !is_int($value)) {
            $ret = '<![CDATA[' . $ret . ']]>';
        }

        return $ret;
    }

    /**
     * Init feed
     *
     * @param \Amasty\Feed\Model\Feed $feed
     * @return $this
     */
    public function initBasics($feed)
    {
        parent::initBasics($feed);

        $this->_header = $feed->getXmlHeader();
        $this->_item = $feed->getXmlItem();
        $this->_footer = $feed->getXmlFooter();

        $this->_parseContent($feed->getXmlContent());

        return $this;
    }

    /**
     * Parse content of feed
     *
     * @param string $content
     * @return void
     */
    protected function _parseContent($content)
    {
        $regex = "#{(.*?)}#";

        preg_match_all($regex, $content, $vars);

        $contentAttributes = [];

        if (isset($vars[self::PREG_FIRST_SUBMASK])) {

            foreach ($vars[self::PREG_FIRST_SUBMASK] as $attributeRow) {
                $attributeParams = [];

                preg_match("/attribute=\"(.*?)\"/", $attributeRow, $attrReg);
                preg_match("/format=\"(.*?)\"/", $attributeRow, $formatReg);
                preg_match("/modify=\"(.*?)\"/", $attributeRow, $lengthReg);
                preg_match("/parent=\"(.*?)\"/", $attributeRow, $parentReg);
                
                if (isset($attrReg[self::PREG_FIRST_SUBMASK])) {
                    $attributeParams = [
                        'attribute' => isset($attrReg[self::PREG_FIRST_SUBMASK]) ? $attrReg[self::PREG_FIRST_SUBMASK] : '',
                        'format' => isset($formatReg[self::PREG_FIRST_SUBMASK]) ? $formatReg[self::PREG_FIRST_SUBMASK] : 'as_is',
                        'modify' => isset($lengthReg[self::PREG_FIRST_SUBMASK]) ? $lengthReg[self::PREG_FIRST_SUBMASK] : '',
                        'parent' => isset($parentReg[self::PREG_FIRST_SUBMASK]) ? $parentReg[self::PREG_FIRST_SUBMASK] : 'no',
                    ];
                }

                $contentAttributes[$attributeRow] = $attributeParams;
            }
        }
        $this->setTagsToDeleteFromContent($content);
        $this->_content = $content;
        $this->_contentAttributes = $contentAttributes;
    }

    /**
     * Clear empty tag
     *
     * @param string &$content
     * @param string $tag
     * @return void
     */
    protected function clearEmptyTag(&$content = '', $tag = '')
    {
        $pattern = '~<' . $tag . '><\/' . $tag . '>' . "\r?\n?~";
        $content = preg_replace($pattern, '', $content);
    }

    /**
     * @param string $content
     */
    private function setTagsToDeleteFromContent($content)
    {
        $regex = '/<(.*)>[\s\S]*?optional="yes"[\s\S]*?<\/\1>/';

        preg_match_all($regex, $content, $matches);

        if (isset($matches[self::PREG_FIRST_SUBMASK])) {
            $this->tagsToRemove = array_merge($this->tagsToRemove, $matches[1]);
        }
    }
}
