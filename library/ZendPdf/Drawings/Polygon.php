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
 * Draw a polygon at the specified position.
 *
 * @package    ZendPdf
 * @subpackage ZendPdf\Drawings
 */
class Polygon extends ShapeAbstract
{
    protected $xCoordinates;
    protected $yCoordinates;

    public function __construct(
                                array $xCoordinates,
                                array $yCoordinates,
                                $fillType = self::DRAW_FILL_AND_STROKE,
                                $fillMethod = self::FILL_METHOD_NON_ZERO_WINDING)
    {
        $this->xCoordinates = $xCoordinates;
        $this->yCoordinates = $yCoordinates;
        $this->fillType = $fillType;
        $this->fillMethod = $fillMethod;
    }

    /**
     * @inheritdoc
     */
    protected function drawElement(Page $page)
    {
        $page->addProcedureSet('PDF');

        $firstPoint = true;
        $path = '';
        $content = '';
        foreach ($this->xCoordinates as $id => $xVal) {
            $xObj = new NumericObject($xVal);
            $yObj = new NumericObject($this->yCoordinates[$id]);

            if ($firstPoint) {
                $path = $xObj->toString() . ' ' . $yObj->toString() . " m\n";
                $firstPoint = false;
            } else {
                $path .= $xObj->toString() . ' ' . $yObj->toString() . " l\n";
            }
        }
        $content .= $path;
        $content .= $this->drawFillType($this->fillType, $this->fillMethod);

        return $content;
    }
}
