<?php
namespace Potato\Compressor\Model\Optimisation\Processor\Finder;

interface IfInterface extends FinderInterface
{
    /**
     * @param string   $haystack
     * @param null|int $start
     * @param null|int $end
     *
     * @return array
     */
    public function findStartTag($haystack, $start = null, $end = null);
    /**
     * @param string   $haystack
     * @param null|int $start
     * @param null|int $end
     *
     * @return array
     */
    public function findEndTag($haystack, $start = null, $end = null);
}