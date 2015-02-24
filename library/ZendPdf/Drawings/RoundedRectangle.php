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
 * Draw a rounded rectangle at the specified position.
 *
 * @package    ZendPdf
 * @subpackage ZendPdf\Drawings
 */
class RoundedRectangle extends ShapeAbstract
{
    const CORNER_TOP_LEFT = 0;
    const CORNER_TOP_RIGHT = 1;
    const CORNER_BOTTOM_RIGHT = 2;
    const CORNER_BOTTOM_LEFT = 3;

    protected $radius = array(
        self::CORNER_TOP_LEFT => 0,
        self::CORNER_TOP_RIGHT => 0,
        self::CORNER_BOTTOM_RIGHT => 0,
        self::CORNER_BOTTOM_LEFT => 0,
    );

    public function __construct($width, $height, $radius, $fillType = self::DRAW_FILL_AND_STROKE)
    {
        $this->width = (float)$width;
        $this->height = (float)$height;
        $this->fillType = $fillType;
        $this->setAllRadius($radius);
    }

    /**
     * @param float $radius
     */
    public function setAllRadius($radius)
    {
        $this->setTopLeft($radius);
        $this->setTopRight($radius);
        $this->setBottomRight($radius);
        $this->setBottomLeft($radius);
    }

    /**
     * @param float $radius
     */
    public function setTopLeft($radius)
    {
        $this->radius[static::CORNER_TOP_LEFT] = (float)$radius;
    }

    /**
     * @param float $radius
     */
    public function setTopRight($radius)
    {
        $this->radius[static::CORNER_TOP_RIGHT] = (float)$radius;
    }

    /**
     * @param float $radius
     */
    public function setBottomRight($radius)
    {
        $this->radius[static::CORNER_BOTTOM_RIGHT] = (float)$radius;
    }

    /**
     * @param float $radius
     */
    public function setBottomLeft($radius)
    {
        $this->radius[static::CORNER_BOTTOM_LEFT] = (float)$radius;
    }

    /**
     * @param Page $page
     * @return string
     */
    protected function drawElement(Page $page)
    {
        $page->addProcedureSet('PDF');

        $topLeftX = $this->xPosition;
        $topLeftY = $this->height;
        $topRightX = $this->width;
        $topRightY = $this->height;
        $bottomRightX = $this->width;
        $bottomRightY = $this->yPosition;
        $bottomLeftX = $this->xPosition;
        $bottomLeftY = $this->yPosition;
        $radius = $this->radius;

        //draw top side
        $x1Obj = new NumericObject($topLeftX + $radius[static::CORNER_TOP_LEFT]);
        $y1Obj = new NumericObject($topLeftY);
        $content = $x1Obj->toString() . ' ' . $y1Obj->toString() . " m\n";
        $x1Obj = new NumericObject($topRightX - $radius[static::CORNER_TOP_RIGHT]);
        $y1Obj = new NumericObject($topRightY);
        $content .= $x1Obj->toString() . ' ' . $y1Obj->toString() . " l\n";

        //draw top right corner if needed
        if ($radius[static::CORNER_TOP_RIGHT] != 0) {
            $x1Obj = new NumericObject($topRightX);
            $y1Obj = new NumericObject($topRightY);
            $x2Obj = new NumericObject($topRightX);
            $y2Obj = new NumericObject($topRightY);
            $x3Obj = new NumericObject($topRightX);
            $y3Obj = new NumericObject($topRightY - $radius[static::CORNER_TOP_RIGHT]);
            $content .= $x1Obj->toString() . ' ' . $y1Obj->toString() . ' '
                . $x2Obj->toString() . ' ' . $y2Obj->toString() . ' '
                . $x3Obj->toString() . ' ' . $y3Obj->toString() . ' '
                . " c\n";
        }

        //draw right side
        $x1Obj = new NumericObject($bottomRightX);
        $y1Obj = new NumericObject($bottomRightY + $radius[static::CORNER_BOTTOM_RIGHT]);
        $content .= $x1Obj->toString() . ' ' . $y1Obj->toString() . " l\n";

        //draw bottom right corner if needed
        if ($radius[static::CORNER_BOTTOM_RIGHT] != 0) {
            $x1Obj = new NumericObject($bottomRightX);
            $y1Obj = new NumericObject($bottomRightY);
            $x2Obj = new NumericObject($bottomRightX);
            $y2Obj = new NumericObject($bottomRightY);
            $x3Obj = new NumericObject($bottomRightX - $radius[static::CORNER_BOTTOM_RIGHT]);
            $y3Obj = new NumericObject($bottomRightY);
            $content .= $x1Obj->toString() . ' ' . $y1Obj->toString() . ' '
                . $x2Obj->toString() . ' ' . $y2Obj->toString() . ' '
                . $x3Obj->toString() . ' ' . $y3Obj->toString() . ' '
                . " c\n";
        }

        //draw bottom side
        $x1Obj = new NumericObject($bottomLeftX + $radius[static::CORNER_BOTTOM_LEFT]);
        $y1Obj = new NumericObject($bottomLeftY);
        $content .= $x1Obj->toString() . ' ' . $y1Obj->toString() . " l\n";

        //draw bottom left corner if needed
        if ($radius[static::CORNER_BOTTOM_LEFT] != 0) {
            $x1Obj = new NumericObject($bottomLeftX);
            $y1Obj = new NumericObject($bottomLeftY);
            $x2Obj = new NumericObject($bottomLeftX);
            $y2Obj = new NumericObject($bottomLeftY);
            $x3Obj = new NumericObject($bottomLeftX);
            $y3Obj = new NumericObject($bottomLeftY + $radius[3]);
            $content .= $x1Obj->toString() . ' ' . $y1Obj->toString() . ' '
                . $x2Obj->toString() . ' ' . $y2Obj->toString() . ' '
                . $x3Obj->toString() . ' ' . $y3Obj->toString() . ' '
                . " c\n";
        }

        //draw left side
        $x1Obj = new NumericObject($topLeftX);
        $y1Obj = new NumericObject($topLeftY - $radius[static::CORNER_TOP_LEFT]);
        $content .= $x1Obj->toString() . ' ' . $y1Obj->toString() . " l\n";

        //draw top left corner if needed
        if ($radius[static::CORNER_TOP_LEFT] != 0) {
            $x1Obj = new NumericObject($topLeftX);
            $y1Obj = new NumericObject($topLeftY);
            $x2Obj = new NumericObject($topLeftX);
            $y2Obj = new NumericObject($topLeftY);
            $x3Obj = new NumericObject($topLeftX + $radius[static::CORNER_TOP_LEFT]);
            $y3Obj = new NumericObject($topLeftY);
            $content .= $x1Obj->toString() . ' ' . $y1Obj->toString() . ' '
                . $x2Obj->toString() . ' ' . $y2Obj->toString() . ' '
                . $x3Obj->toString() . ' ' . $y3Obj->toString() . ' '
                . " c\n";
        }

        $content .= $this->drawFillType($this->fillType);
        return $content;
    }
}
