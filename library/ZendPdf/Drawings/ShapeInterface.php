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
 * Draw a line of text at the specified position.
 *
 * @package    ZendPdf
 * @subpackage ZendPdf\Drawings
 */
interface ShapeInterface extends DrawingInterface
{
    /**
     * Stroke the path only. Do not fill.
     */
    const DRAW_STROKE = 0;

    /**
     * Fill the path only. Do not stroke.
     */
    const DRAW_FILL = 1;

    /**
     * Fill and stroke the path.
     */
    const DRAW_FILL_AND_STROKE = 2;

}
