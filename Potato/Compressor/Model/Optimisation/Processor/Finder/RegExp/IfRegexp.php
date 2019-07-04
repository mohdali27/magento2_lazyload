<?php
namespace Potato\Compressor\Model\Optimisation\Processor\Finder\RegExp;

use Potato\Compressor\Model\Optimisation\Processor\Finder\IfInterface;
use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Raw;

class IfRegexp extends AbstractRegexp implements IfInterface
{
    protected $startTagNeedles = array(
        '/<!-{0,2}\[if[^\]]*\]\s*>(\s*<!-->)*/is',
    );
    protected $endTagNeedles = array(
        '/(<!--\s*)*<!\[endif\]-{0,2}>/is',
    );

    /**
     * @param string   $haystack
     * @param null|int $start
     * @param null|int $end
     *
     * @return array
     * @throws \Exception
     */
    public function findStartTag($haystack, $start = null, $end = null)
    {
        $result = array();
        foreach ($this->startTagNeedles as $needle) {
            $result = array_merge($result, $this->findByNeedle($needle, $haystack, $start, $end));
        }
        return array_values($result);
    }

    /**
     * @param string   $haystack
     * @param null|int $start
     * @param null|int $end
     *
     * @return array
     * @throws \Exception
     */
    public function findEndTag($haystack, $start = null, $end = null)
    {
        $result = array();
        foreach ($this->endTagNeedles as $needle) {
            $result = array_merge($result, $this->findByNeedle($needle, $haystack, $start, $end));
        }
        return array_values($result);
    }

    /**
     * @param string   $haystack
     * @param null|int $start
     * @param null|int $end
     *
     * @return array
     * @throws \Exception
     */
    public function findAll($haystack, $start = null, $end = null)
    {
        $startTagResult = $this->findStartTag($haystack, $start, $end);
        $endTagResult = $this->findEndTag($haystack, $start, $end);

        $result = array_merge($startTagResult, $endTagResult);
        uasort($result, [$this, 'sortByStartPos']);
        return array_values($result);
    }

    /**
     * @param Raw $a
     * @param Raw $b
     *
     * @return int
     */
    private function sortByStartPos($a, $b)
    {
        return $a->getStart() - $b->getStart();
    }
}
