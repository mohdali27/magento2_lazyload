<?php
namespace Potato\Compressor\Model\Optimisation\Processor\Finder\RegExp;

use Potato\Compressor\Model\Optimisation\Processor\Finder\ImageInterface;
use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Tag;

class Image extends AbstractRegexp implements ImageInterface
{
    protected $needles = [
        "/<img[^>]+?src\\s*=\\s*['\"].*?['\"][^>]*?>/is"
    ];

    /**
     * @param string $source
     * @param int    $pos
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
}