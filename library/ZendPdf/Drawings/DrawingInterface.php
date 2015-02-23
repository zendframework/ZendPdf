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

use ZendPdf\Page;
use ZendPdf\Style;

/**
 * Drawing Interface
 *
 * @package    ZendPdf
 * @subpackage ZendPdf\Drawings
 */
interface DrawingInterface extends PositionInterface
{
    /**
     * Set the style to use for current drawing operation
     * @param Style $style
     * @return void
     */
    public function setStyle(Style $style);

    /**
     * Translate drawing into Pdf elements.
     * @param Page $page
     * @return string
     */
    public function draw(Page $page);
}
