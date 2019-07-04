<?php
namespace Potato\Compressor\Model\Optimisation\Processor\Finder\Result;

class Tag extends Raw
{
    /** @var array */
    protected $attributes = [];

    public function __construct($content, $start, $end)
    {
        parent::__construct($content, $start, $end);
        //find attributes of tag
        $dom = new \DOMDocument();
        @$dom->loadHTML('<html><body>' . $content . '</body></html>');
        $xml = simplexml_import_dom($dom);
        $tag = $xml->xpath('body/*');
        foreach ($tag[0]->attributes() as $key => $value) {
            $this->attributes[$key] = $value->__toString();
        }
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasAttribute($key)
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * @param $attributes
     *
     * @return string
     */
    public function getContentWithUpdatedAttribute($attributes)
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML('<html><body>' . $this->getContent() . '</body></html>');
        $xpath = new \DOMXpath($dom);
        $tagList = $xpath->query('/html/body/*');
        $tag = $tagList->item(0);
        foreach ($attributes as $key => $value) {
            $tag->setAttribute($key, $value);
        }
        return $dom->saveHTML($tag);
    }
}