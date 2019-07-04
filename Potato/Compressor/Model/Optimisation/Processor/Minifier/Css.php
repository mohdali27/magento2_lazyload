<?php
namespace Potato\Compressor\Model\Optimisation\Processor\Minifier;

use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Tag;
use Potato\Compressor\Helper\File as FileHelper;
use Potato\Compressor\Helper\Data as DataHelper;
use Potato\Compressor\Helper\Css as CssHelper;
use Magento\Framework\App\RequestInterface;

/**
 * Class Css
 */
class Css extends AbstractMinifier
{
    /** @var CssHelper  */
    protected $cssHelper;

    /**
     * Css constructor.
     * @param FileHelper $fileHelper
     * @param DataHelper $dataHelper
     * @param RequestInterface $request
     * @param CssHelper $cssHelper
     */
    public function __construct(
        FileHelper $fileHelper,
        DataHelper $dataHelper,
        RequestInterface $request,
        CssHelper $cssHelper
    ) {
        parent::__construct($fileHelper, $dataHelper, $request);
        $this->cssHelper = $cssHelper;

    }
    
    /**
     * @param string $content
     *
     * @return string
     */
    public function minifyContent($content)
    {
        $result = \Potato\Compressor\Lib\Minify\CSS::minify($content, array('preserveComments' => false));
        return $result;
    }

    /**
     * @param string $file
     * @param string $content
     *
     * @return string
     */
    protected function beforeMinifyFile($file, $content)
    {
        return $this->cssHelper->unifyUrlInCssContent($file, $content);
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function getTargetFilename($file)
    {
        return md5($file) . '.css';
    }

    /**
     * @param Tag $tag
     *
     * @return string
     */
    protected function getPathFromTag($tag)
    {
        $attributes = $tag->getAttributes();
        return $attributes['href'];
    }
}
