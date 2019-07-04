<?php
namespace Potato\Compressor\Model\Optimisation\Processor\Minifier;

use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Tag;
use Potato\Compressor\Lib\Minify\JShrink as LibJShrink;

/**
 * Class Js
 */
class Js extends AbstractMinifier
{
    /**
     * @param string $content
     *
     * @return string
     * @throws \Exception
     */
    public function minifyContent($content)
    {
        return LibJShrink::minify($content, array('flaggedComments' => false));
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function getTargetFilename($file)
    {
        return md5($file) . '.js';
    }

    /**
     * @param Tag $tag
     *
     * @return string
     */
    protected function getPathFromTag($tag)
    {
        $attributes = $tag->getAttributes();
        return $attributes['src'];
    }
}