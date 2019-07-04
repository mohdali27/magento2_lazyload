<?php
namespace Potato\Compressor\Model\Optimisation\Processor\Finder\RegExp;

use Potato\Compressor\Model\Optimisation\Processor\Finder\JsInterface;
use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Raw;
use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Tag;

class Js extends AbstractRegexp implements JsInterface
{
    protected $needles = array(
        "<script[^>\w]*?>.*?<\/script>",
        "<script[^>]*?src=[^>]+?>.*?<\/script>",
        "<script[^>]*?[\"']text\/javascript[\"'][^>]*?>.*?<\/script>",
        "<script[^>]*?[\"']application\/javascript[\"'][^>]*?>.*?<\/script>",
        "<script[^>]*?[\"']javascript[\"'][^>]*?>.*?<\/script>",
    );

    /** @var HtmlComment */
    protected $htmlCommentFinder = null;

    /**
     * @param HtmlComment $htmlComment
     */
    public function __construct(
        HtmlComment $htmlComment
    ) {
        $this->htmlCommentFinder = $htmlComment;
    }

    /**
     * @param string $haystack
     * @param null|int $start
     * @param null|int $end
     *
     * @return Tag[]
     * @throws \Exception
     */
    public function findInline($haystack, $start = null, $end = null)
    {
        $result = $this->findAll($haystack, $start, $end);
        foreach ($result as $key => $tag) {
            /** @var Tag $tag */
            $attributes = $tag->getAttributes();
            if (array_key_exists('src', $attributes)) {
                unset($result[$key]);
            }
        }
        return array_values($result);
    }

    /**
     * @param string $haystack
     * @param null|int $start
     * @param null|int $end
     *
     * @return Tag[]
     * @throws \Exception
     */
    public function findExternal($haystack, $start = null, $end = null)
    {
        $result = $this->findAll($haystack, $start, $end);
        foreach ($result as $key => $tag) {
            /** @var Tag $tag */
            $attributes = $tag->getAttributes();
            if (!array_key_exists('src', $attributes)) {
                unset($result[$key]);
            }
        }
        return array_values($result);
    }

    /**
     * @param string $haystack
     * @param null $start
     * @param null $end
     *
     * @return Tag[]
     * @throws \Exception
     */
    public function findAll($haystack, $start = null, $end = null)
    {
        $pattern = "/" . join('|', $this->needles) . "/is";
        /** @var Tag[] $result */
        $result = $this->findByNeedle($pattern, $haystack, $start, $end);
        $result = $this->excludeTagsWhichWithinHtmlComment($result, $haystack);
        return array_values($result);
    }

    /**
     * @param string $haystack
     * @param string[] $typeList
     * @param null|int $start
     * @param null|int $end
     *
     * @return Tag[]
     * @throws \Exception
     */
    public function findAllExceptTypes($haystack, $typeList, $start = null, $end = null)
    {
        $tagList = $this->findAll($haystack, $start, $end);
        foreach ($tagList as $key => $tag) {
            $attributes = $tag->getAttributes();
            if (!array_key_exists('type', $attributes)) {
                continue;
            }
            $tagType = trim($attributes['type']);
            if (in_array($tagType, $typeList)) {
                unset($tagList[$key]);
            }
        }
        return array_values($tagList);
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
     * @param Tag[] $tagList
     * @param string $haystack
     *
     * @return Tag[]
     * @throws \Exception
     */
    protected function excludeTagsWhichWithinHtmlComment($tagList, $haystack)
    {
        $htmlCommentList = $this->htmlCommentFinder->findAll($haystack);
        foreach ($tagList as $key => $tag) {
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
}
