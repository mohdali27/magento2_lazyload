<?php
namespace Potato\Compressor\Model\Optimisation\Processor;

use Potato\Compressor\Model\Optimisation\Processor\Finder\RegExp\IfRegexp;
use Potato\Compressor\Helper\Data as DataHelper;
use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Raw;
use Potato\Compressor\Helper\HtmlParser;
use Potato\Compressor\Lib\Minify\HTMLMax as LibHTMLMax;

class Html
{
    /** @var IfRegexp  */
    protected $ifFinder = null;

    public function __construct(IfRegexp $ifFinder)
    {
        $this->ifFinder = $ifFinder;
    }

    /**
     * @param string $html
     *
     * @return $this
     */
    public function compress(&$html)
    {
        $result = LibHTMLMax::minify($html, array('jsCleanComments' => false));
        if (strlen($result) > 0) {
            $html = $result;
        }
        return $this;
    }

    /**
     * @param string $html
     *
     * @return $this
     */
    public function removeIgnoreFlag(&$html)
    {
        $ignoreFlagList = [
            DataHelper::FLAG_IGNORE_ALL,
            DataHelper::FLAG_IGNORE_MOVE,
            DataHelper::FLAG_IGNORE_MINIFY
        ];
        $cutData = [];
        $pattern = '/<[^>]*{{flag}}[\b=>]+/is';
        foreach ($ignoreFlagList as $ignoreFlag) {
            $findAllPattern = str_replace('{{flag}}', $ignoreFlag, $pattern);
            preg_match_all($findAllPattern, $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
            if (count($matches) === 0) {
                continue;
            }
            foreach ($matches as $match) {
                $match = $match[0];
                $start = $match[1] + strpos($match[0], $ignoreFlag);
                preg_match('/<[^>]+>/is', $html, $haystack, PREG_OFFSET_CAPTURE, $match[1]);
                $haystack = $haystack[0];
                $length = strlen($ignoreFlag);
                $attributePattern = '/' . $ignoreFlag . '[\s]*=[\s]*[\'"][^\'"]*[\'"]/is';
                if (preg_match($attributePattern, $haystack[0], $attributeMatch)) {
                    $length = strlen($attributeMatch[0]);
                }
                $cutData[$start] = array(
                    'start' => $start,
                    'end' => $start + $length - 1
                );
            }
        }
        ksort($cutData);
        foreach (array_reverse($cutData) as $cutElData) {
            $html = HtmlParser::cutFromHtml(
                $html, $cutElData['start'], $cutElData['end']
            );
        }
        return $this;
    }

    /**
     * @param string $html
     *
     * @return $this
     * @throws \Exception
     */
    public function removeEmptyIfDirective(&$html)
    {
        $startTagList = $this->ifFinder->findStartTag($html);
        $endTagList = $this->ifFinder->findEndTag($html);
        $cutData = [];
        foreach ($startTagList as $key => $startTag) {
            /** @var Raw $startTag */
            /** @var Raw $endTag */
            if (!array_key_exists($key, $endTagList)) {
                return $this;
            }
            $endTag = $endTagList[$key];
            $startPos = $startTag->getEnd();
            $endPos = $endTag->getStart();
            $content = substr($html, $startPos, $endPos - $startPos);

            if (preg_match('/\w/is', $content) === 0) {
                $cutData[$startTag->getStart()] = [
                    'start' => $startTag->getStart(),
                    'end'   => $endTag->getEnd()
                ];
            }
        }
        ksort($cutData);
        foreach (array_reverse($cutData) as $cutElData) {
            $html = HtmlParser::cutFromHtml(
                $html, $cutElData['start'], $cutElData['end']
            );
        }
        return $this;
    }

    /**
     * @param string $html
     *
     * @return $this
     * @throws \Exception
     */
    public function removeDuplicateIfTags(&$html)
    {
        $startTagList = $this->ifFinder->findStartTag($html);
        $tagListByContent = [];
        foreach ($startTagList as $tag) {
            /** @var Raw $tag */
            $tagListByContent[md5($tag->getContent())][] = $tag;
        }
        $cutData = [];
        foreach ($tagListByContent as $group) {
            if (count($group) < 2) {
                continue;
            }
            for ($i = 1; $i < count($group); $i++) {
                /** @var Raw $previousTag */
                $previousTag = $group[$i-1];
                /** @var Raw $currentTag */
                $currentTag = $group[$i];
                $endTagList = $this->ifFinder->findEndTag($html, $previousTag->getEnd(), $currentTag->getStart());
                if (count($endTagList) === 0) {
                    continue;
                }
                /** @var Raw $previousEndTag */
                $previousEndTag = reset($endTagList);
                $betweenText = HtmlParser::getStringFromHtml(
                    $html, $previousEndTag->getEnd() + 1, $currentTag->getStart()
                );
                if (0 === preg_match('/\w/is', $betweenText)) {
                    //if nothing found between equal if tags then it must be merged
                    //just save cut information there
                    $cutData[] = [
                        'start' => $previousEndTag->getStart(),
                        'end' => $previousEndTag->getEnd(),
                    ];
                    $cutData[] = [
                        'start' => $currentTag->getStart(),
                        'end' => $currentTag->getEnd(),
                    ];
                }
            }
        }
        //remove from html
        foreach (array_reverse($cutData) as $cutElData) {
            $html = HtmlParser::cutFromHtml(
                $html, $cutElData['start'], $cutElData['end']
            );
        }
        return $this;
    }
}
