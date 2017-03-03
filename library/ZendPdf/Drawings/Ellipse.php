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
 * Draw an ellipse at the specified position.
 *
 * @package    ZendPdf
 * @subpackage ZendPdf\Drawings
 */
class Ellipse extends ShapeAbstract
{
    /**
     * @var float
     */
    protected $startAngle;
    /**
     * @var float
     */
    protected $endAngle;

    public function __construct($width, $height, $startAngle = null, $endAngle = null, $fillType = self::DRAW_FILL_AND_STROKE)
    {
        $this->width = (float)$width;
        $this->height = (float)$height;
        $this->fillType = $fillType;
        $this->startAngle = (float)$startAngle;
        $this->endAngle = (float)$endAngle;
    }

    protected function drawElement(Page $page)
    {
        $startAngle = $this->startAngle;
        $endAngle = $this->endAngle;
        $xCoordinate = $this->xPosition;
        $yCoordinate = $this->yPosition;
        $width = $this->width;
        $height = $this->height;

        $page->addProcedureSet('PDF');

        if ($width < $xCoordinate) {
            $temp = $xCoordinate;
            $xCoordinate = $width;
            $width = $temp;
        }
        if ($height < $yCoordinate) {
            $temp = $yCoordinate;
            $yCoordinate = $height;
            $height = $temp;
        }

        $x = ($xCoordinate + $width) / 2.;
        $y = ($yCoordinate + $height) / 2.;

        $content = '';

        if ($startAngle !== .0) {
            $content .= $this->drawClipSector(
                $x,
                $y,
                $width,
                $height,
                $xCoordinate,
                $yCoordinate,
                $startAngle,
                $endAngle
            );
        }

        $content .= $this->drawEllipse($x, $y, $width, $height, $xCoordinate, $yCoordinate);
        $content .= $this->drawFillType($this->fillType);
        $content .= $this->drawCloseEllipse();

        return $content;
    }

    /**
     * Get close clip if have start angle.
     * @return string
     */
    private function drawCloseEllipse()
    {
        if ($this->startAngle !== .0) {
            return 'Q' . PHP_EOL;
        }
        return '';
    }

    /**
     * @param float $x
     * @param float $y
     * @param float $width
     * @param float $height
     * @param float $xCoordinate
     * @param float $yCoordinate
     * @param float $startAngle
     * @param float $endAngle
     * @return string
     */
    private function drawClipSector($x, $y, $width, $height, $xCoordinate, $yCoordinate, $startAngle, $endAngle)
    {
        $xC = new NumericObject($x);
        $yC = new NumericObject($y);

        $startAngle = fmod($startAngle, M_PI * 2);
        $endAngle = fmod($endAngle, M_PI * 2);

        if ($startAngle > $endAngle) {
            $endAngle += M_PI * 2;
        }

        $clipPath = $xC->toString() . ' ' . $yC->toString() . " m\n";
        $clipSectors = (int)ceil(($endAngle - $startAngle) / M_PI_4);
        $clipRadius = max($width - $xCoordinate, $height - $yCoordinate);

        for ($count = 0; $count <= $clipSectors; $count++) {
            $pAngle = $startAngle + ($endAngle - $startAngle) * $count / (float)$clipSectors;

            $pX = new NumericObject($x + cos($pAngle) * $clipRadius);
            $pY = new NumericObject($y + sin($pAngle) * $clipRadius);
            $clipPath .= $pX->toString() . ' ' . $pY->toString() . " l\n";
        }

        return "q\n" . $clipPath . "h\nW\nn\n";
    }

    /**
     * @param float $x
     * @param float $y
     * @param float $width
     * @param float $height
     * @param float $xCoordinate
     * @param float $yCoordinate
     * @return string
     */
    private function drawEllipse($x, $y, $width, $height, $xCoordinate, $yCoordinate)
    {
        $xC = new NumericObject($x);
        $yC = new NumericObject($y);

        $xLeft = new NumericObject($xCoordinate);
        $xRight = new NumericObject($width);
        $yUp = new NumericObject($height);
        $yDown = new NumericObject($yCoordinate);

        $xDelta = 2 * (M_SQRT2 - 1) * ($width - $xCoordinate) / 3.;
        $yDelta = 2 * (M_SQRT2 - 1) * ($height - $yCoordinate) / 3.;
        $xr = new NumericObject($x + $xDelta);
        $xl = new NumericObject($x - $xDelta);
        $yu = new NumericObject($y + $yDelta);
        $yd = new NumericObject($y - $yDelta);

        return $xC->toString() . ' ' . $yUp->toString() . " m\n"
        . $xr->toString() . ' ' . $yUp->toString() . ' '
        . $xRight->toString() . ' ' . $yu->toString() . ' '
        . $xRight->toString() . ' ' . $yC->toString() . " c\n"
        . $xRight->toString() . ' ' . $yd->toString() . ' '
        . $xr->toString() . ' ' . $yDown->toString() . ' '
        . $xC->toString() . ' ' . $yDown->toString() . " c\n"
        . $xl->toString() . ' ' . $yDown->toString() . ' '
        . $xLeft->toString() . ' ' . $yd->toString() . ' '
        . $xLeft->toString() . ' ' . $yC->toString() . " c\n"
        . $xLeft->toString() . ' ' . $yu->toString() . ' '
        . $xl->toString() . ' ' . $yUp->toString() . ' '
        . $xC->toString() . ' ' . $yUp->toString() . " c\n";
    }
}
