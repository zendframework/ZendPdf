<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Pdf
 */

namespace ZendPdf\Drawings;

/**
 * Position interface
 *
 * @package    ZendPdf
 * @subpackage ZendPdf\Drawings
 */
interface PositionInterface
{

    /**
     * Set X and Y position.
     * @param float $x
     * @param float $y
     * @return void
     */
    public function setPosition($x, $y);

    /**
     * Set X position.
     * @param float $position
     * @return void
     */
    public function setX($position);

    /**
     * Set Y position.
     * @param float $position
     * @return void
     */
    public function setY($position);
}
