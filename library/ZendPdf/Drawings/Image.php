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
 * Draw a rectangle at the specified position.
 *
 * @package    ZendPdf
 * @subpackage ZendPdf\Drawings
 */
class Image extends DrawingAbstract
{
    protected $image;
    protected $width;
    protected $height;

    public function __construct(AbstractImage $image, $width, $height)
    {
        $this->image = $image;
        $this->width = (float)$width;
        $this->height = (float)$height;
    }

    /**
     * @inheritdoc
     */
    protected function drawElement(Page $page)
    {
        $page->addProcedureSet('PDF');

        $imageName = $page->attachResource('XObject', $this->image);
        $imageNameObj = new NameObject($imageName);

        $x1Obj = new NumericObject($this->xPosition);
        $y1Obj = new NumericObject($this->yPosition);
        $widthObj = new NumericObject($this->width - $this->xPosition);
        $heightObj = new NumericObject($this->height - $this->yPosition);

        return "q\n"
        . '1 0 0 1 ' . $x1Obj->toString() . ' ' . $y1Obj->toString() . " cm\n"
        . $widthObj->toString() . ' 0 0 ' . $heightObj->toString() . " 0 0 cm\n"
        . $imageNameObj->toString() . " Do\n"
        . "Q\n";
    }
}
