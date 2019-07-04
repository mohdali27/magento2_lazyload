<?php
namespace Potato\Compressor\Model\Optimisation\Processor;

use Potato\Compressor\Helper\Data as HelperData;
use Potato\Compressor\Helper\File as FileHelper;
use Potato\Compressor\Model\Config;
use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Raw;
use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Tag;
use Potato\Compressor\Helper\HtmlParser;

class Css
{
    const DEFAULT_MEDIA_VALUE = 'all';

    /**
     * @var array
     */
    protected $ignoreMoveFlagList = [
        HelperData::FLAG_IGNORE_ALL,
        HelperData::FLAG_IGNORE_MOVE
    ];

    /**
     * @var array
     */
    protected $ignoreMergeFlagList = [
        HelperData::FLAG_IGNORE_ALL,
        HelperData::FLAG_IGNORE_MOVE,
        HelperData::FLAG_IGNORE_MERGE,
        HelperData::FLAG_IGNORE_MINIFY
    ];

    /**
     * @var array
     */
    protected $ignoreMinifyFlagList = [
        HelperData::FLAG_IGNORE_ALL,
        HelperData::FLAG_IGNORE_MINIFY
    ];

    /** @var null|Finder\RegExp\Css  */
    protected $tagFinder = null;

    /** @var null|Finder\RegExp\IfRegexp  */
    protected $ifFinder = null;

    /** @var null|Merger\Css  */
    protected $merger = null;

    /** @var null|Minifier\Css  */
    protected $minifier = null;

    /** @var FileHelper  */
    protected $fileHelper;

    /** @var Config  */
    protected $config;

    public function __construct(
        Finder\RegExp\Css $cssRegexp,
        Finder\RegExp\IfRegexp $ifRegexp,
        Merger\Css $cssMerger,
        Minifier\Css $cssMinifier,
        FileHelper $fileHelper,
        Config $config
    ) {
        $this->tagFinder = $cssRegexp;
        $this->ifFinder = $ifRegexp;
        $this->merger = $cssMerger;
        $this->minifier = $cssMinifier;
        $this->config = $config;
        $this->fileHelper = $fileHelper;
    }

    /**
     * @param string $html
     *
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function inline(&$html)
    {
        $tagList = $this->tagFinder->findExternal($html);
        $this->excludeIgnoreTagFromList($tagList, $this->ignoreMoveFlagList);
        if (count($tagList) === 0) {
            return $this;
        }
        $replaceData = array();
        foreach ($tagList as $tag) {
            /** @var Tag $tag */
            $attributes = $tag->getAttributes();
            if (!$this->fileHelper->isInternalUrl($attributes['href'])) {
                continue;
            }
            $path = $this->fileHelper->getLocalPathFromUrl($attributes['href']);
            if (!file_exists($path)) {
                continue;
            }
            $contentLength = $this->fileHelper->getStringLengthFromUrl($attributes['href']);
            if ($contentLength > HelperData::INLINE_MAX_LENGTH) {
                continue;
            }
            $content = $this->fileHelper->getFileContentByUrl($attributes['href']);
            if (!strlen($content) || strlen($content) > HelperData::INLINE_MAX_LENGTH) {
                continue;
            }
            $media = $this->getMediaFromTag($tag);
            $replaceData[] = [
                'start' => $tag->getStart(),
                'end' => $tag->getEnd(),
                'content' => '<style type="text/css" media="' . $media . '">' . $content . '</style>'
            ];
        }
        foreach (array_reverse($replaceData) as $replaceElData) {
            $html = HtmlParser::replaceIntoHtml(
                $html, $replaceElData['content'], $replaceElData['start'], $replaceElData['end']
            );
        }
        return $this;
    }

    /**
     * @param string $html
     *
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function merge(&$html)
    {
        if ($this->config->isCssMergeInlineEnabled()) {
            $tagList = $this->tagFinder->findAll($html);
        } else {
            $tagList = $this->tagFinder->findExternal($html);
        }
        if (count($tagList) === 0) {
            return $this;
        }
        $this->removeDuplicate($tagList);
        /** @var Tag $firstTag */
        $firstTag = reset($tagList);
        $groupList = [
            $firstTag->getStart() => [$firstTag]
        ];

        for ($i = 1; $i < count($tagList); $i++) {
            /** @var Tag $previousTag */
            $previousTag = $tagList[$i-1];
            /** @var Tag $currentTag */
            $currentTag = $tagList[$i];

            $isNeedNewGroup = false;
            //group by tags (one group can not have tags between group elements)
            $start = $previousTag->getEnd() + 1;
            $length = $currentTag->getStart() - $start;
            $betweenText = substr($html, $start, $length);
            if (1 === preg_match('/<[^>]+?>/is', $betweenText)) {
                $isNeedNewGroup = true;
            }
            //group by ignore attributes
            $isPreviousTagMustBeIgnored = HtmlParser::isTagMustBeIgnored(
                $previousTag->getContent(), $this->ignoreMergeFlagList, $this->config->getExcludeAnchors()
            );
            $isCurrentTagMustBeIgnored = HtmlParser::isTagMustBeIgnored(
                $currentTag->getContent(), $this->ignoreMergeFlagList, $this->config->getExcludeAnchors()
            );
            if ($isPreviousTagMustBeIgnored || $isCurrentTagMustBeIgnored) {
                //if can not be merged
                $isNeedNewGroup = true;
            }

            //group by "media" attribute
            $previousAttributes = $previousTag->getAttributes();

            $currentAttributes = $currentTag->getAttributes();
            $previousMedia = self::DEFAULT_MEDIA_VALUE;//by default
            $currentMedia = self::DEFAULT_MEDIA_VALUE;//by default

            if (array_key_exists('media', $previousAttributes)) {
                $previousMedia = $previousAttributes['media'];
            }
            if (array_key_exists('media', $currentAttributes)) {
                $currentMedia = $currentAttributes['media'];
            }
            if ($previousMedia !== $currentMedia) {
                //if media attribute is not equal
                $isNeedNewGroup = true;
            }
            //group by url from external website
            $previousAttributes = $previousTag->getAttributes();
            if (array_key_exists('href', $previousAttributes)) {
                if (!$this->fileHelper->isInternalUrl($previousAttributes['href'])) {
                    $isNeedNewGroup = true;
                }
                if (!file_exists($this->fileHelper->getLocalPathFromUrl($previousAttributes['href']))) {
                    $isNeedNewGroup = true;
                }
            }
            $currentAttributes = $currentTag->getAttributes();
            if (array_key_exists('href', $currentAttributes)) {
                if (!$this->fileHelper->isInternalUrl($currentAttributes['href'])) {
                    $isNeedNewGroup = true;
                }
                if (!file_exists($this->fileHelper->getLocalPathFromUrl($currentAttributes['href']))) {
                    $isNeedNewGroup = true;
                }
            }
            if ($isNeedNewGroup) {
                //start new group
                $groupList[$currentTag->getStart()] = [$currentTag];
                next($groupList);
                continue;
            }
            $groupList[key($groupList)][] = $currentTag;
        }
        $replaceData = [];
        foreach ($groupList as $key => $group) {
            if (count($group) < 2) {
                continue;
            }
            $mergedUrl = $this->merger->merge($group);
            if (null === $mergedUrl) {
                continue;
            }
            /** @var Tag $firstTag */
            $firstTag = reset($group);
            /** @var Tag $lastTag */
            $lastTag = end($group);
            $media = $this->getMediaFromTag($firstTag);
            $replaceData[] = [
                'start' => $firstTag->getStart(),
                'end'   => $lastTag->getEnd(),
                'url'   => $mergedUrl,
                'media' => $media
            ];
        }
        foreach (array_reverse($replaceData) as $replaceElData) {
            $replacement = '<link rel="stylesheet" type="text/css" media="' . $replaceElData['media']
                . '" href="' . $replaceElData['url'] . '" />'
            ;
            $html = HtmlParser::replaceIntoHtml(
                $html, $replacement, $replaceElData['start'], $replaceElData['end']
            );
        }
        return $this;
    }

    /**
     * @param string $html
     *
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function compress(&$html)
    {
        $tagList = $this->tagFinder->findExternal($html);
        $this->excludeIgnoreTagFromList($tagList, $this->ignoreMinifyFlagList);
        $replaceData = array();
        foreach ($tagList as $tag) {
            /** @var Tag $tag */
            $minifiedUrl = $this->minifier->minify($tag);
            if (null === $minifiedUrl) {
                continue;
            }
            $media = $this->getMediaFromTag($tag);
            $replaceData[$tag->getStart()] = [
                'start' => $tag->getStart(),
                'end'   => $tag->getEnd(),
                'content' => '<link rel="stylesheet" type="text/css" media="' . $media
                    . '" href="' . $minifiedUrl . '" />'
            ];
        }
        $tagList = $this->tagFinder->findInline($html);
        $this->excludeIgnoreTagFromList($tagList, $this->ignoreMinifyFlagList);
        foreach ($tagList as $tag) {
            /** @var Tag $tag */
            $content = preg_replace('/^<style[^>]*>/', '', $tag->getContent());
            $content = preg_replace('/<\/style>$/', '', $content);
            $minContent = $this->minifier->minifyContent($content);
            if (strlen(trim($minContent)) === 0) {//if empty result
                continue;
            }
            $media = $this->getMediaFromTag($tag);
            $replaceData[$tag->getStart()] = [
                'start' => $tag->getStart(),
                'end' => $tag->getEnd(),
                'content' => '<style type="text/css" media="' . $media . '">' . $minContent . '</style>'
            ];
        }
        ksort($replaceData);
        foreach (array_reverse($replaceData) as $replaceElData) {
            $html = HtmlParser::replaceIntoHtml(
                $html, $replaceElData['content'], $replaceElData['start'], $replaceElData['end']
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
    public function moveToBodyEnd(&$html)
    {
        $tagList = $this->tagFinder->findAll($html);
        $this->excludeIgnoreTagFromList($tagList, $this->ignoreMoveFlagList);

        $startIfList = $this->ifFinder->findStartTag($html);
        $endIfList = $this->ifFinder->findEndTag($html);
        /** @var Raw $startIf */
        /** @var Raw $endIf */
        $startIf = current($startIfList);
        $endIf = current($endIfList);
        $cutData = [];
        foreach ($tagList as $tag) {
            /** @var Tag $tag */
            $cutTagData = [
                'start' => $tag->getStart(),
                'end' => $tag->getEnd(),
                'content' => $tag->getContent()
            ];
            //find "if" for tag
            while ($startIf && $endIf && $tag->getStart() > $endIf->getEnd()) {
                $startIf = next($startIfList);
                $endIf = next($endIfList);
            }

            if (
                $startIf && $endIf
                && $tag->getStart() > $startIf->getEnd()
                && $tag->getEnd() < $endIf->getStart()
            ) {
                $cutTagData['content'] = $startIf->getContent() . $cutTagData['content'] . $endIf->getContent();
            }
            $cutData[] = $cutTagData;
        }

        $resultString = "";
        foreach (array_reverse($cutData) as $cutElData) {
            $resultString = $cutElData['content'] . "\n" . $resultString;
            $html = HtmlParser::cutFromHtml($html, $cutElData['start'], $cutElData['end']);
        }
        $html = HtmlParser::insertStringBeforeBodyEnd($resultString, $html);
        return $this;
    }

    /**
     * @param array $tagList
     *
     * @return $this
     */
    protected function removeDuplicate(&$tagList)
    {
        //place to remove duplicates (equal libs and etc)
        return $this;
    }

    /**
     * @param array $tagList
     * @param array $ignoreFlagList
     *
     * @return $this
     */
    protected function excludeIgnoreTagFromList(&$tagList, $ignoreFlagList)
    {
        foreach ($tagList as $key => $tag) {
            /** @var Tag $tag */
            $isTagMustBeIgnored = HtmlParser::isTagMustBeIgnored(
                $tag->getContent(), $ignoreFlagList, $this->config->getExcludeAnchors()
            );
            if ($isTagMustBeIgnored) {
                unset($tagList[$key]);
            }
        }
        return $this;
    }

    /**
     * @param Tag $tag
     *
     * @return string
     */
    protected function getMediaFromTag($tag)
    {
        $media = self::DEFAULT_MEDIA_VALUE;
        $tagAttributes = $tag->getAttributes();
        if (array_key_exists('media', $tagAttributes)) {
            $media = $tagAttributes['media'];
        }
        return $media;
    }
}
