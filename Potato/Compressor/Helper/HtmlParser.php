<?php
namespace Potato\Compressor\Helper;

class HtmlParser
{
    /**
     * @param string $html
     * @param string $replacement
     * @param int $start
     * @param int $end
     *
     * @return string
     */
    public static function replaceIntoHtml($html, $replacement, $start, $end)
    {
        $length = $end - $start + 1;
        return substr_replace($html, $replacement, $start, $length);
    }

    /**
     * @param string $html
     * @param int $start
     * @param int $end
     *
     * @return string
     */
    public static function cutFromHtml($html, $start, $end)
    {
        return self::replaceIntoHtml($html, '', $start, $end);
    }

    /**
     * @param string $insertString
     * @param string $targetHtml
     *
     * @return string
     */
    public static function insertStringBeforeBodyEnd($insertString, $targetHtml)
    {
        return str_replace('</body>', $insertString . "\n</body>", $targetHtml);
    }

    /**
     * @param string $insertString
     * @param string $targetHtml
     *
     * @return string
     */
    public static function insertStringBeforeHeadEnd($insertString, $targetHtml)
    {
        return str_replace('</head>', $insertString . "\n</head>", $targetHtml);
    }

    /**
     * @param string $html
     * @param int $start
     * @param int $end
     *
     * @return string
     */
    public static function getStringFromHtml($html, $start, $end)
    {
        $length = $end - $start;
        return substr($html, $start, $length);
    }

    /**
     * @param string $html
     * @param string $text
     *
     * @return bool
     */
    public static function isHtmlContainText($html, $text)
    {
        return strpos($html, $text) !== FALSE;
    }

    /**
     * @param string $html
     *
     * @return bool
     */
    public static function isHtml($html)
    {
        $result = self::isHtmlContainText($html, '<html');
        $result = $result && self::isHtmlContainText($html, '</html>');
        $result = $result && self::isHtmlContainText($html, '<body');
        $result = $result && self::isHtmlContainText($html, '</body>');
        return $result;
    }

    /**
     * @param string $tagHtml
     * @param array $ignoreTagList
     * @param array $anchorList
     *
     * @return bool
     */
    public static function isTagMustBeIgnored($tagHtml, $ignoreTagList = array(), $anchorList = array())
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML('<html><body>' . $tagHtml . '</body></html>');
        $xml = simplexml_import_dom($dom);
        $tag = $xml->xpath('/html/body/*');
        $tag = $tag[0];
        $attributes = array();
        foreach ($tag->attributes() as $key => $value) {
            $attributes[$key] = $value->__toString();
        }
        if (count(array_intersect(array_keys($attributes), $ignoreTagList)) > 0) {
            return true;
        }
        $haystack = null;
        switch (strtolower($tag->getName())) {
            case 'script':
                $haystack = $tag->__toString();
                if (array_key_exists('src', $attributes)) {
                    $haystack = $attributes['src'];
                }
                break;
            case 'link':
                if (array_key_exists('href', $attributes)) {
                    $haystack = $attributes['href'];
                }
                break;
            case 'style':
                $haystack = $tag->__toString();
                break;
        }
        if (null === $haystack) {
            return false;
        }
        foreach ($anchorList as $anchor) {
            if (FALSE !== strpos($haystack, $anchor)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $tagHtml
     *
     * @return string
     */
    public static function getContentFromTag($tagHtml)
    {
        $pattern = "/^<[^>]+>(.*)<[^<]+>$/is";
        $result = preg_match($pattern, $tagHtml, $matches);
        if (!$result || !array_key_exists(1, $matches)) {
            //old code
            $dom = new \DOMDocument();
            @$dom->loadHTML(
                '<?xml encoding="utf-8" ?>'
                . '<html><body>' . $tagHtml . '</body></html>'
            );
            $xml = simplexml_import_dom($dom);
            $tag = $xml->xpath('/html/body/*');
            $tag = $tag[0];
            return $tag->__toString();
        }
        return $matches[1];
    }
}
