<?php
namespace Potato\Compressor\Model\Optimisation\Processor\Finder\RegExp;

use Potato\Compressor\Model\Optimisation\Processor\Finder\CssInterface;
use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Raw;
use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Tag;

class Css extends AbstractRegexp implements CssInterface
{
    protected $inlineNeedles = array(
        '/<style.*?>.*?<\/style>/is'
    );
    protected $externalNeedles = array(
        "/<link[^>]+?rel\\s*=\\s*['\"]stylesheet['\"]+?[^>]*>/is"
    );

    /** @var Js */
    protected $jsFinder = null;

    /** @var HtmlComment */
    protected $htmlCommentFinder = null;

    /**
     * Css constructor.
     * @param Js $jsFinder
     * @param HtmlComment $htmlComment
     */
    public function __construct(
        Js $jsFinder,
        HtmlComment $htmlComment
    ) {
        $this->jsFinder = $jsFinder;
        $this->htmlCommentFinder = $htmlComment;
    }

    /**
     * @param string   $haystack
     * @param null|int $start
     * @param null|int $end
     *
     * @return array
     * @throws \Exception
     */
    public function findInline($haystack, $start = null, $end = null)
    {
        $result = [];
        foreach ($this->inlineNeedles as $needle) {
            $result = array_merge($result, $this->findByNeedle($needle, $haystack, $start, $end));
        }
        $result = $this->excludeTagsWhichWithinJsTag($result, $haystack);
        $result = $this->excludeTagsWhichWithinHtmlComment($result, $haystack);
        return array_values($result);
    }

    /**
     * @param string   $haystack
     * @param null|int $start
     * @param null|int $end
     *
     * @return array
     * @throws \Exception
     */
    public function findExternal($haystack, $start = null, $end = null)
    {
        $result = [];
        foreach ($this->externalNeedles as $needle) {
            $result = array_merge($result, $this->findByNeedle($needle, $haystack, $start, $end));
        }
        $result = $this->excludeTagsWhichWithinJsTag($result, $haystack);
        $result = $this->excludeTagsWhichWithinHtmlComment($result, $haystack);
        return array_values($result);
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
        $inlineResult = $this->findInline($haystack, $start, $end);
        $externalResult = $this->findExternal($haystack, $start, $end);
        $result = array_merge($inlineResult, $externalResult);
        uasort($result, [$this, 'sortByStartPos']);
        return array_values($result);
    }

    /**
     * @param string $source
     * @param int $pos
     *
     * @return Tag
     * @throws \Exception
     */
    protected function processResult($source, $pos)
    {
        $raw = parent::processResult($source, $pos);
        $result = new Tag(
            $raw->getContent(), $raw->getStart(), $raw->getEnd()
        );
        return $result;
    }

    /**
     * @param array $tagList
     * @param string $haystack
     *
     * @return array
     * @throws \Exception
     */
    protected function excludeTagsWhichWithinJsTag($tagList, $haystack)
    {
        $jsTagList = $this->jsFinder->findInline($haystack);
        foreach ($tagList as $key => $tag) {
            /** @var Tag $tag */
            $start = $tag->getStart();
            foreach ($jsTagList as $jsTag) {
                /** @var Tag $jsTag */
                if ($jsTag->getStart() < $start && $jsTag->getEnd() > $start) {
                    unset($tagList[$key]);
                    break;
                }
            }
        }
        return $tagList;
    }

    /**
     * @param array $tagList
     * @param string $haystack
     *
     * @return array
     * @throws \Exception
     */
    protected function excludeTagsWhichWithinHtmlComment($tagList, $haystack)
    {
        $htmlCommentList = $this->htmlCommentFinder->findAll($haystack);
        foreach ($tagList as $key => $tag) {
            /** @var Tag $tag */
            $start = $tag->getStart();
            foreach ($htmlCommentList as $htmlComment) {
                /** @var Raw $htmlComment */
                if ($htmlComment->getStart() < $start && $htmlComment->getEnd() > $start) {
                    unset($tagList[$key]);
                    break;
                }
            }
        }
        return $tagList;
    }

    /**
     * @param Raw $a
     * @param Raw $b
     *
     * @return int
     */
    private function sortByStartPos($a, $b)
    {
        return $a->getStart() - $b->getStart();
    }
}
