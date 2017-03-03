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

use ZendPdf\InternalType\NumericObject;
use ZendPdf\Page;

/**
 * Draw a rectangle at the specified position.
 *
 * @package    ZendPdf
 * @subpackage ZendPdf\Drawings
 */
class Rectangle extends ShapeAbstract
{

    public function __construct($width, $height, $fillType = self::DRAW_FILL_AND_STROKE)
    {
        $this->width = (float)$width;
        $this->height = (float)$height;
        $this->fillType = $fillType;
    }

    /**
     * @inheritdoc
     */
    protected function drawElement(Page $page)
    {
        $page->addProcedureSet('PDF');

        $xCoordinate = new NumericObject($this->xPosition);
        $yCoordinate = new NumericObject($this->yPosition);
        $width = new NumericObject($this->width - $this->xPosition);
        $height = new NumericObject($this->height - $this->yPosition);

        $content = $xCoordinate->toString() . ' ' . $yCoordinate->toString() . ' '
            . $width->toString() . ' ' . $height->toString() . " re\n";

        $content .= $this->drawFillType($this->fillType);
        return $content;
    }
}
