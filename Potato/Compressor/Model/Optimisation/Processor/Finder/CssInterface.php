<?php
namespace Potato\Compressor\Model\Optimisation\Processor\Finder;

interface CssInterface extends FinderInterface
{
    /**
     * @param string   $haystack
     * @param null|int $start
     * @param null|int $end
     *
     * @return array
     */
    public function findInline($haystack, $start = null, $end = null);
    /**
     * @param string   $haystack
     * @param null|int $start
     * @param null|int $end
     *
     * @return array
     */
    public function findExternal($haystack, $start = null, $end = null);
}