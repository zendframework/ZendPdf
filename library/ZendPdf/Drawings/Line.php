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

use ZendPdf\InternalType\NameObject;
use ZendPdf\InternalType\NumericObject;
use ZendPdf\Page;
use ZendPdf\Resource\Image\AbstractImage;

/**
 * Draw a line at the specified position.
 *
 * @package    ZendPdf
 * @subpackage ZendPdf\Drawings
 */
class Line extends DrawingAbstract
{
    protected $horizontalPoint;
    protected $verticalPoint;

    public function __construct($horizontalPoint, $verticalPoint)
    {
        $this->horizontalPoint = (float)$horizontalPoint;
        $this->verticalPoint = (float)$verticalPoint;
    }

    /**
     * @inheritdoc
     */
    protected function drawElement(Page $page)
    {
        $page->addProcedureSet('PDF');

        $x1Obj = new NumericObject($this->xPosition);
        $y1Obj = new NumericObject($this->yPosition);
        $x2Obj = new NumericObject($this->horizontalPoint);
        $y2Obj = new NumericObject($this->verticalPoint);

        return $x1Obj->toString() . ' ' . $y1Obj->toString() . " m\n"
            .  $x2Obj->toString() . ' ' . $y2Obj->toString() . " l\n S\n";

    }
}
