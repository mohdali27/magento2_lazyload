<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Config\Source;

class NumberFormat
{
    /**
     * Points constants
     */
    const ONE_POINT = 'one';
    const TWO_POINTS = 'two';
    const THREE_POINTS = 'three';
    const FOUR_POINTS = 'four';

    /**
     * Separate constants
     */
    const DOT = 'dot';
    const COMMA = 'comma';
    const SPACE = 'space';

    /**
     * @return array
     */
    public function getAllDecimals()
    {
        return $decimals = [
            self::ONE_POINT => 1,
            self::TWO_POINTS => 2,
            self::THREE_POINTS => 3,
            self::FOUR_POINTS => 4
        ];
    }

    /**
     * @return array
     */
    public function getAllSeparators()
    {
        return $separators = [
            self::DOT => '.',
            self::COMMA => ',',
            self::SPACE => ' ',
        ];
    }
}
