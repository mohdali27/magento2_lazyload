<?php
namespace Potato\Compressor\Model\Optimisation\Processor\Finder\DOM;

use Potato\Compressor\Model\Optimisation\Processor\Finder\FinderInterface;
use Potato\Compressor\Model\Optimisation\Processor\Finder\RegExp\HtmlComment;
use Potato\Compressor\Model\Optimisation\Processor\Finder\RegExp\ScriptTag;
use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Raw;
use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Tag;

/** IMPORTANT: the class works only with non-pair tags, like <br>, <img> */
abstract class AbstractDom implements FinderInterface
{
    protected $needles = array();

    /** @var HtmlComment */
    protected $htmlCommentFinder = null;

    /** @var ScriptTag */
    protected $scriptTagFinder = null;

    public function __construct(
        HtmlComment $htmlComment,
        ScriptTag $scriptTagFinder
    ) {
        $this->htmlCommentFinder = $htmlComment;
        $this->scriptTagFinder = $scriptTagFinder;
    }

    /**
     * @param string   $haystack
     * @param null|int $start
     * @param null|int $end
     *
     * @return array
     * @throws \Exception
     */
    public function findAll($haystack, $start = null, $end = null)
    {
        $result = [];
        foreach ($this->needles as $needle) {
            $result = array_merge($result, $this->findByNeedle($needle, $haystack, $start, $end));
        }
        return $result;
    }

    /**
     * @param string $needle
     * @param string $haystack
     * @param null|int $start
     * @param null $end
     *
     * @return array
     * @throws \Exception
     */
    protected function findByNeedle(
        $needle, $haystack, $start = null, $end = null
    ) {
        $needle = strtolower($needle);
        $html = $haystack;
        if (null !== $start || null !== $end) {
            if (null === $start) {
                $start = 0;
            }
            $length = null;
            if (null !== $end) {
                $length = $end - $start;
            }
            $html = mb_substr($haystack, $start, $length);
        }
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);//skip E_WARNING
        $xpath = new \DOMXpath($dom);
        $tagList = $xpath->query($needle);
        $tagListByNodeName = [];
        $regExpListByNodeName = [];
        $result = [];
        foreach ($tagList as $tag) {
            /** @var \DOMNode $tag */
            if (!array_key_exists($tag->nodeName, $tagListByNodeName)) {
                $xpathByNodeName = new \DOMXpath($dom);
                $tagListByNodeName[$tag->nodeName] = $xpathByNodeName->query('//' . $tag->nodeName);
            }
            if (!array_key_exists($tag->nodeName, $regExpListByNodeName)) {
                preg_match_all(
                    '/<' . $tag->nodeName . '\s[^>]*>/is', $html,
                    $matches, PREG_OFFSET_CAPTURE
                );
                $regExpListByNodeName[$tag->nodeName] = $this->excludeMatchesWhichWithinHtmlComment(
                    $matches[0], $html
                );
                $regExpListByNodeName[$tag->nodeName] = $this->excludeMatchesWhichWithinScriptComment(
                    $regExpListByNodeName[$tag->nodeName], $html
                );
            }
            $currentTagList = $tagListByNodeName[$tag->nodeName];
            if ($currentTagList->length !== count($regExpListByNodeName[$tag->nodeName])) {
                //if something wrong
                continue;
            }
            $regExpTag = null;
            foreach ($tagListByNodeName[$tag->nodeName] as $key => $nodeTag) {
                if (!$tag->isSameNode($nodeTag)) {
                    continue;
                }
                $regExpTag = $regExpListByNodeName[$tag->nodeName][$key];
                break;
            }
            if (null === $regExpTag) {
                continue;
            }
            $result[] = $this->processResult($regExpTag[0], $regExpTag[1]);
        }
        return $result;
    }

    /**
     * @param string $source
     * @param int    $pos
     *
     * @return Raw
     * @throws \Exception
     */
    protected function processResult($source, $pos)
    {
        $end = $pos + strlen($source) - 1;
        $result = new Raw(
            $source, $pos, $end
        );
        return $result;
    }

    /**
     * @param array $matches
     * @param string $haystack
     *
     * @return array
     * @throws \Exception
     */
    protected function excludeMatchesWhichWithinHtmlComment($matches, $haystack)
    {
        $htmlCommentList = $this->htmlCommentFinder->findAll($haystack);
        foreach ($matches as $key => $match) {
            /** @var Tag $tag */
            $start = $match[1];
            foreach ($htmlCommentList as $htmlComment) {
                /** @var Raw $htmlComment */
                if ($htmlComment->getStart() < $start && $htmlComment->getEnd() > $start) {
                    unset($matches[$key]);
                    break;
                }
            }
        }
        return array_values($matches);
    }

    /**
     * @param array $matches
     * @param string $haystack
     *
     * @return array
     * @throws \Exception
     */
    protected function excludeMatchesWhichWithinScriptComment($matches, $haystack)
    {
        $scriptTagList = $this->scriptTagFinder->findAll($haystack);
        foreach ($matches as $key => $match) {
            $start = $match[1];
            foreach ($scriptTagList as $scriptTag) {
                /** @var Raw $scriptTag */
                if ($scriptTag->getStart() < $start && $scriptTag->getEnd() > $start) {
                    unset($matches[$key]);
                    break;
                }
            }
        }
        return array_values($matches);
    }
}
