<?php
namespace Potato\Compressor\Model\Optimisation\Processor\Finder\RegExp;

use Potato\Compressor\Model\Optimisation\Processor\Finder\FinderInterface;

class HtmlComment extends AbstractRegexp implements FinderInterface
{
    protected $needles = [
        "/<!--[^\[>].*?-->/is",
    ];
}
