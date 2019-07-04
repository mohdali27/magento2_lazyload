<?php
namespace Potato\Compressor\Model\Optimisation\Processor\Finder\DOM;

use Potato\Compressor\Model\Optimisation\Processor\Finder\ImageInterface;
use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Tag;

class Image extends AbstractDom implements ImageInterface
{
    protected $needles = array(
        '//img[@src]' //get all img tags with src attribute
    );

    /**
     * @param string $xpath
     * @param string $haystack
     * @param null $start
     * @param null $end
     *
     * @return array
     * @throws \Exception
     */
    public function findByXPath($xpath, $haystack, $start = null, $end = null)
    {
        return $this->findByNeedle($xpath, $haystack, $start, $end);
    }

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
