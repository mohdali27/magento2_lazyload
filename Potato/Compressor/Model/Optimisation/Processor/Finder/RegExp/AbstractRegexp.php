<?php
namespace Potato\Compressor\Model\Optimisation\Processor\Finder\RegExp;

use Potato\Compressor\Model\Optimisation\Processor\Finder\FinderInterface;
use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Raw;

abstract class AbstractRegexp implements FinderInterface
{
    /** @var array */
    protected $needles = [];

    /**
     * @param string $haystack
     * @param null|int $start
     * @param null|int $end
     *
     * @return Raw[]
     * @throws \Exception
     */
    public function findAll($haystack, $start = null, $end = null)
    {
        $result = [];
        foreach ($this->needles as $needle) {
            $result = array_merge($result, $this->findByNeedle($needle, $haystack, $start, $end));
        }
        return array_values($result);
    }

    /**
     * @param string $needle
     * @param string $haystack
     * @param null|int $start
     * @param null $end
     *
     * @return Raw[]
     * @throws \Exception
     */
    protected function findByNeedle(
        $needle, $haystack, $start = null, $end = null
    ) {
        $findResult = preg_match_all(
            $needle, $haystack, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE
        );
        if (FALSE === $findResult) {
            throw new \Exception('preg_match_all error in RegExp\Abstract, error code: ' . preg_last_error());
        }
        $result = [];
        foreach ($matches as $match) {
            $match = $match[0];
            $pos = $match[1];
            if (null !== $start && $start > $pos) {
                continue;
            }
            if (null !== $end && $end < $pos) {
                continue;
            }
            $result[] = $this->processResult($match[0], $pos);
        }
        return $result;
    }

    /**
     * @param string $source
     * @param int $pos
     *
     * @return Raw
     * @throws \Exception
     */
    protected function processResult($source, $pos)
    {
        $end = $pos + strlen($source) - 1;
        $result = new Raw(
            $source, $pos, $end
        );
        return $result;
    }
}