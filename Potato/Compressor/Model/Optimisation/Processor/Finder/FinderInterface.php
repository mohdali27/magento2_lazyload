<?php
namespace Potato\Compressor\Model\Optimisation\Processor\Finder;

interface FinderInterface
{
    /**
     * @param string $haystack
     * @param null|int $start
     * @param null|int $end
     *
     * @return mixed
     */
    public function findAll($haystack, $start = null, $end = null);
}