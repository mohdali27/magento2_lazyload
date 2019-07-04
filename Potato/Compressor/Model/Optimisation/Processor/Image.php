<?php
namespace Potato\Compressor\Model\Optimisation\Processor;

use Potato\Compressor\Model\Optimisation\Processor\Finder\DOM\Image as DomImage;
use Potato\Compressor\Model\Optimisation\Processor\Finder\RegExp\Css as RegExpCss;
use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Raw;
use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Tag;
use Potato\Compressor\Helper\Css as CssHelper;
use Potato\Compressor\Helper\File as FileHelper;
use Potato\Compressor\Helper\Image as ImageHelper;
use Potato\Compressor\Helper\Data as DataHelper;
use Potato\Compressor\Helper\HtmlParser;
use Magento\Framework\App\CacheInterface as AppCache;

class Image
{
    const CSS_IMAGE_MERGE_CACHE_KEY = 'POTATO_IMAGE_MERGE_CSS_FILE';

    /** @var null|DomImage  */
    protected $tagFinder = null;

    /** @var null|RegExpCss  */
    protected $cssTagFinder = null;

    /** @var CssHelper  */
    protected $cssHelper;

    /** @var FileHelper  */
    protected $fileHelper;

    /** @var ImageHelper  */
    protected $imageHelper;

    /** @var AppCache */
    protected $appCache;

    /**
     * @param DomImage $domImage
     * @param RegExpCss $regexpCss
     * @param CssHelper $cssHelper
     * @param FileHelper $fileHelper
     * @param ImageHelper $imageHelper
     * @param AppCache $appCache
     */
    public function __construct(
        DomImage $domImage,
        RegExpCss $regexpCss,
        CssHelper $cssHelper,
        FileHelper $fileHelper,
        ImageHelper $imageHelper,
        AppCache $appCache
    ) {
        $this->tagFinder = $domImage;
        $this->cssTagFinder = $regexpCss;
        $this->cssHelper = $cssHelper;
        $this->fileHelper = $fileHelper;
        $this->imageHelper = $imageHelper;
        $this->appCache = $appCache;
    }

    /**
     * @param string $html
     *
     * @return $this
     * @throws \Exception
     */
    public function processLazyLoad(&$html)
    {
        $replaceData = [];
        $tagList = $this->tagFinder->findAll($html);
        foreach ($tagList as $tag) {
            /** @var Tag $tag */
            $attributes = $tag->getAttributes();
            if (!array_key_exists('src', $attributes)) {
                continue;
            }
            if (strpos(trim($attributes['src']), 'data:image') === 0) {//if inline image
                continue;
            }
            if (array_key_exists('data-po-cmp-ignore', $attributes)) {
                continue;
            }
            $replaceData[] = [
                'start'   => $tag->getStart(),
                'end'     => $tag->getEnd(),
                'content' => $tag->getContentWithUpdatedAttribute(
                    [
                        'data-po-cmp-src' => $attributes['src'],
                        'src'             => DataHelper::IMAGE_PLACEHOLDER
                    ]
                )
            ];
        }
        uasort($replaceData, [$this, 'sortByStartPos']);
        $replaceData = array_values($replaceData);
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
    public function processFixIdenticalContent(&$html)
    {
        $urlList = [];
        $tagList = $this->tagFinder->findAll($html);
        foreach ($tagList as $tag) {
            /** @var Tag $tag */
            $attributes = $tag->getAttributes();
            if (!array_key_exists('src', $attributes)) {
                continue;
            }
            if (array_key_exists('data-po-cmp-ignore', $attributes)) {
                continue;
            }
            $url = trim($attributes['src']);
            if (!$this->fileHelper->isInternalUrl($url)) {
                continue;
            }
            $path = $this->fileHelper->getLocalPathFromUrl($url);
            if (!file_exists($path)) {
                continue;
            }
            $index = filesize($path);
            if (FALSE === $index) {
                continue;
            }
            if (!array_key_exists($index, $urlList)) {
                $urlList[$index] = [];
            }
            $urlList[$index][] = [
                'tag' => $tag,
                'src' => trim($attributes['src']),
            ];
        }
        $replaceData = [];
        foreach ($urlList as $index => $list) {
            if (count($list) < 2) {
                continue;
            }
            $contentList = [];
            foreach ($list as $key => $item) {
                $contentList[$key] = $this->fileHelper->getFileContentByUrl($item['src']);
            }
            $identicalKeyList = [];
            foreach ($contentList as $aKey => $aContent) {
                foreach ($contentList as $bKey => $bContent) {
                    if ($aKey === $bKey || $aContent !== $bContent) {
                        continue;
                    }
                    if (!array_key_exists($aKey, $identicalKeyList)) {
                        $identicalKeyList[$aKey] = [];
                    } else if (null === $identicalKeyList[$aKey]) {
                        continue;//do not duplicate info also for B items
                    }
                    $identicalKeyList[$aKey][] = $bKey;
                    $identicalKeyList[$bKey] = null;
                }
            }
            foreach ($identicalKeyList as $key => $identicalList) {
                if (!is_array($identicalList)) {
                    continue;
                }
                $canonUrl = $list[$key]['src'];
                foreach ($identicalList as $identicalKey) {
                    $tag =$list[$identicalKey]['tag'];
                    $replaceData[] = [
                        'start'   => $tag->getStart(),
                        'end'     => $tag->getEnd(),
                        'content' => $tag->getContentWithUpdatedAttribute(
                            [
                                'src' => $canonUrl
                            ]
                        )
                    ];
                }

            }
        }
        uasort($replaceData, [$this, 'sortByStartPos']);
        $replaceData = array_values($replaceData);
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
    public function processCSSImageMerge(&$html)
    {
        $replaceData = [];
        $tagList = $this->cssTagFinder->findAll($html);
        foreach ($tagList as $tag) {
            /** @var Tag $tag */
            $attributes = $tag->getAttributes();
            if (array_key_exists('href', $attributes)) {
                $cacheKey = self::CSS_IMAGE_MERGE_CACHE_KEY . '+' . md5($attributes['href']);
                if ($this->appCache->load($cacheKey)) {
                    continue;
                }
                if (!$this->fileHelper->isLocalStaticUrl($attributes['href'])) {
                    continue;
                }
                $filePath = $this->fileHelper->getLocalPathFromUrl($attributes['href']);
                $content = $this->fileHelper->getFileContentByUrl($attributes['href']);
                $content = $this->cssHelper->inlineImagesByContent($content);
                $this->fileHelper->putContentInFile($content, $filePath);
                $this->appCache->save('1', $cacheKey, array(DataHelper::COMPRESSOR_CACHE_TAG));
                continue;
            } else {
                $content = $this->cssHelper->inlineImagesByContent($tag->getContent());
            }
            $replaceData[] = [
                'start'   => $tag->getStart(),
                'end'     => $tag->getEnd(),
                'content' => $content
            ];
        }
        $replaceData = array_values($replaceData);
        foreach (array_reverse($replaceData) as $replaceElData) {
            $html = HtmlParser::replaceIntoHtml(
                $html, $replaceElData['content'], $replaceElData['start'], $replaceElData['end']
            );
        }
        return $this;
    }

    /**
     * @param Raw $a
     * @param Raw $b
     *
     * @return int
     */
    private function sortByStartPos($a, $b)
    {
        return $a['start'] - $b['start'];
    }
}
