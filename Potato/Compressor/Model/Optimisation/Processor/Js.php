<?php
namespace Potato\Compressor\Model\Optimisation\Processor;

use Potato\Compressor\Helper\Data as HelperData;
use Potato\Compressor\Helper\File as FileHelper;
use Potato\Compressor\Model\Config;
use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Raw;
use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Tag;
use Potato\Compressor\Helper\HtmlParser;
use Potato\Compressor\Model\RequireJsManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

class Js
{
    /** @var array */
    protected $ignoreMoveFlagList = [
        HelperData::FLAG_IGNORE_ALL,
        HelperData::FLAG_IGNORE_MOVE
    ];

    /** @var array */
    protected $ignoreMergeFlagList = [
        HelperData::FLAG_IGNORE_ALL,
        HelperData::FLAG_IGNORE_MOVE,
        HelperData::FLAG_IGNORE_MERGE,
        HelperData::FLAG_IGNORE_MINIFY
    ];

    /** @var array */
    protected $ignoreMinifyFlagList = [
        HelperData::FLAG_IGNORE_ALL,
        HelperData::FLAG_IGNORE_MINIFY
    ];

    /** @var array */
    protected $notCompressScriptTypeList = [
        'text/x-custom-template',
        'application/ld+json',
        'text/x-magento-template'
    ];

    /** @var null|Finder\RegExp\Js  */
    protected $tagFinder = null;

    /** @var null|Finder\RegExp\ScriptTag  */
    protected $scriptTagFinder = null;

    /** @var null|Finder\RegExp\IfRegexp  */
    protected $ifFinder = null;

    /** @var null|Merger\Js  */
    protected $merger = null;

    /** @var null|Minifier\Js  */
    protected $minifier = null;

    /** @var FileHelper  */
    protected $fileHelper;

    /** @var HelperData  */
    protected $dataHelper;

    /** @var Config  */
    protected $config;

    /** @var RequireJsManager */
    protected $requireJsManager;

    /** @var RequestInterface  */
    protected $request;

    /** @var ResponseInterface  */
    protected $response;

    public function __construct(
        Finder\RegExp\Js $jsRegexp,
        Finder\RegExp\IfRegexp $ifRegexp,
        Finder\RegExp\ScriptTag $scriptTagFinder,
        Merger\Js $jsMerger,
        Minifier\Js $jsMinifier,
        HelperData $dataHelper,
        FileHelper $fileHelper,
        Config $config,
        RequireJsManager $requireJsManager,
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $this->tagFinder = $jsRegexp;
        $this->ifFinder = $ifRegexp;
        $this->scriptTagFinder = $scriptTagFinder;
        $this->merger = $jsMerger;
        $this->minifier = $jsMinifier;
        $this->config = $config;
        $this->dataHelper = $dataHelper;
        $this->fileHelper = $fileHelper;
        $this->requireJsManager = $requireJsManager;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @param string $html
     *
     * @return $this
     * @throws \Exception
     */
    public function moveToBodyEnd(&$html)
    {
        $tagList = $this->scriptTagFinder->findAll($html);
        $this->excludeIgnoreTagFromList($tagList, $this->ignoreMoveFlagList);

        $startIfList = $this->ifFinder->findStartTag($html);
        $endIfList = $this->ifFinder->findEndTag($html);
        /** @var Raw $startIf */
        /** @var Raw $endIf */
        $startIf = current($startIfList);
        $endIf = current($endIfList);

        $cutData = array();
        foreach ($tagList as $tag) {
            /** @var Tag $tag */
            $cutTagData = array(
                'start' => $tag->getStart(),
                'end' => $tag->getEnd(),
                'content' => $tag->getContent()
            );
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
            $resultString = $cutElData['content'] . $resultString;
            $html = HtmlParser::cutFromHtml($html, $cutElData['start'], $cutElData['end']);
        }
        $html = HtmlParser::insertStringBeforeBodyEnd($resultString, $html);
        return $this;
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
            if (!$this->fileHelper->isInternalUrl($attributes['src'])) {
                continue;
            }
            $contentLength = $this->fileHelper->getStringLengthFromUrl($attributes['src']);
            if ($contentLength > HelperData::INLINE_MAX_LENGTH) {
                continue;
            }
            $content = $this->fileHelper->getFileContentByUrl($attributes['src']);
            if (!strlen($content) || strlen($content) > HelperData::INLINE_MAX_LENGTH) {
                continue;
            }
            $replaceData[] = array(
                'start' => $tag->getStart(),
                'end' => $tag->getEnd(),
                'content' => '<script type="text/javascript">' . $content . '</script>'
            );
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
        $this->insertRequireJsFiles($html);
        if ($this->config->isJsMergeInlineEnabled()) {
            $tagList = $this->tagFinder->findAll($html);
        } else {
            $tagList = $this->tagFinder->findExternal($html);
        }
        if (count($tagList) === 0) {
            return $this;
        }
        /** @var Tag $firstTag */
        $firstTag = reset($tagList);
        $groupList = array(
            $firstTag->getStart() => array($firstTag)
        );
        for ($i = 1; $i < count($tagList); $i++) {
            /** @var Tag $previousTag */
            $previousTag = $tagList[$i-1];
            /** @var Tag $currentTag */
            $currentTag = $tagList[$i];
            $isNeedNewGroup = false;
            //group by tags (one group can not have tags between group elements)
            $betweenText = HtmlParser::getStringFromHtml(
                $html, $previousTag->getEnd() + 1, $currentTag->getStart()
            );
            if (1 === preg_match('/<[^>]+?>/is', $betweenText)) {
                $isNeedNewGroup = true;
            }
            //group by ignore attributes
            $isCurrentTagMustBeIgnored = HtmlParser::isTagMustBeIgnored(
                $currentTag->getContent(), $this->ignoreMergeFlagList, $this->config->getExcludeAnchors()
            );
            $isPreviousTagMustBeIgnored = HtmlParser::isTagMustBeIgnored(
                $previousTag->getContent(), $this->ignoreMergeFlagList, $this->config->getExcludeAnchors()
            );
            if ($isPreviousTagMustBeIgnored || $isCurrentTagMustBeIgnored) {
                //if can not be merged
                $isNeedNewGroup = true;
            };
            //group by url from external website
            $previousAttributes = $previousTag->getAttributes();
            if (array_key_exists('src', $previousAttributes)
                && !$this->fileHelper->isInternalUrl($previousAttributes['src'])) {
                $isNeedNewGroup = true;
            }
            $currentAttributes = $currentTag->getAttributes();
            if (array_key_exists('src', $currentAttributes)
                && !$this->fileHelper->isInternalUrl($currentAttributes['src'])) {
                $isNeedNewGroup = true;
            }
            if ($isNeedNewGroup) {
                //start new group
                $groupList[$currentTag->getStart()] = array($currentTag);
                next($groupList);
                continue;
            }
            $groupList[key($groupList)][] = $currentTag;
        }
        $replaceData = array();
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
            $replaceData[] = array(
                'start' => $firstTag->getStart(),
                'end' => $lastTag->getEnd(),
                'url' => $mergedUrl
            );
        }
        foreach (array_reverse($replaceData) as $replaceElData) {
            $replacement = '<script type="text/javascript" src="' . $replaceElData['url'] . '"></script>';
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
     * @throws \Exception
     */
    public function compress(&$html)
    {
        $tagList = $this->scriptTagFinder->findAllExceptTypes($html, $this->notCompressScriptTypeList);
        if (count($tagList) === 0) {
            return $this;
        }
        $this->excludeIgnoreTagFromList($tagList, $this->ignoreMinifyFlagList);
        $replaceData = array();
        foreach ($tagList as $tag) {
            /** @var Tag $tag */
            $attributes = $tag->getAttributes();
            if (array_key_exists('src', $attributes)) {//if external
                $minifiedUrl = $this->minifier->minify($tag);
                if (null === $minifiedUrl) {
                    continue;
                }
                $replaceData[] = array(
                    'start' => $tag->getStart(),
                    'end' => $tag->getEnd(),
                    'content' => $tag->getContentWithUpdatedAttribute([
                        'src' => $minifiedUrl
                    ])
                );
            } else {//if inline
                preg_match('/^(<script[^>]*?>)(.*)(<\/script>)$/is', $tag->getContent(), $matches);
                if (count($matches) === 0) {
                    continue;
                }
                $content = $matches[2];
                $minContent = $this->minifier->minifyContent($content);
                if (strlen(trim($minContent)) === 0) {//if empty result
                    continue;
                }
                $replaceData[] = array(
                    'start' => $tag->getStart(),
                    'end' => $tag->getEnd(),
                    'content' => $matches[1] . $minContent . $matches[3]
                );
            }
        }
        foreach (array_reverse($replaceData) as $replaceElData) {
            $html = HtmlParser::replaceIntoHtml(
                $html, $replaceElData['content'], $replaceElData['start'], $replaceElData['end']
            );
        }
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
     * @param $html
     *
     * @return $this
     * @throws \Exception
     */
    protected function insertRequireJsFiles(&$html)
    {
        $key = null;
        $requireJsList = null;
        $tagList = $this->tagFinder->findInline($html);
        foreach ($tagList as $tag) {
            /** @var Tag $tag */
            $attributes = $tag->getAttributes();
            if (!array_key_exists(RequireJsManager::SCRIPT_TAG_DATA_KEY, $attributes)) {
                continue;
            }
            $key = $attributes[RequireJsManager::SCRIPT_TAG_DATA_KEY];
            break;
        }
        if (null === $key) {
            return $this;
        }
        if (!$this->requireJsManager->isDataExists($key)) {
            //do not cache page by Varnish if page does not have final js content
            $this->response->setNoCacheHeaders();
            return $this;
        }
        $jsTagList = $this->tagFinder->findExternal($html);
        foreach ($jsTagList as $tag) {
            /** @var Tag $tag */
            $attributes = $tag->getAttributes();
            $src = $attributes['src'];
            //url ex requirejs/require.js?tas=v20180514
            if (!preg_match('/requirejs\/require\.(min\.)?js/', $src)) {
                continue;
            }
            $filePath = $this->getRequireJsResultFilePath($key);
            if (!file_exists($filePath)) {
                $this->fileHelper->putContentInFile(
                    $this->requireJsManager->getRequireJsContent($key), $filePath
                );
            }
            $urlToFile = $this->getRequireJsResultUrl($key);
            $urlToLib = $this->dataHelper->getRequireJsBuildScriptUrl($src);
            $insertString = $tag->getContent()
                . "\n"
                . "<script type='text/javascript' src='$urlToFile'></script>"
                . "<script type='text/javascript' src='$urlToLib'></script>"
            ;
            $html = HtmlParser::replaceIntoHtml($html, $insertString, $tag->getStart(), $tag->getEnd());
            break;
        }
        return $this;
    }

    /**
     * @param string $key
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRequireJsResultFilePath($key)
    {
        $fileDir = $this->dataHelper->getRootCachePath() . DIRECTORY_SEPARATOR .
            HelperData::REQUIREJS_STORAGE_DIR . DIRECTORY_SEPARATOR
        ;
        return $fileDir . $this->getRequireJsResultFileName($key);
    }

    /**
     * @param string $key
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getRequireJsResultUrl($key)
    {
        return $this->dataHelper->getRootCacheUrl($this->request->isSecure())
            . '/' . HelperData::REQUIREJS_STORAGE_DIR
            . '/' . $this->getRequireJsResultFileName($key)
        ;
    }

    /**
     * @param string $key
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getRequireJsResultFileName($key)
    {
        $urlList = $this->requireJsManager->loadUrlList($key);
        sort($urlList);
        $name = md5(implode(',', $urlList));
        return md5(
            $key . '||' . $name . '||' . $this->fileHelper->getLastFileChangeTimestampForUrlList($urlList)
        ). '.js';
    }
}
