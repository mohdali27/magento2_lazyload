<?php
namespace Potato\Compressor\Model\Optimisation\Processor\Finder\RegExp;

class ScriptTag extends Js
{
    protected $needles = array(
        "<script[^>]*?>.*?<\/script>"
    );
}