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
abstract class ShapeAbstract extends DrawingAbstract implements ShapeInterface
{
    /**
     * @var float
     */
    protected $width;
    /**
     * @var float
     */
    protected $height;
    /**
     * @var int
     */
    protected $fillType;

    /**
     * @param float $width
     * @return ShapeAbstract
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @param float $height
     * @return ShapeAbstract
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @param int $fillType
     * @return ShapeAbstract
     */
    public function setFillType($fillType)
    {
        $this->fillType = $fillType;
        return $this;
    }

    /**
     * @param int $fillType
     * @return string
     */
    protected function drawFillType($fillType)
    {
        $content = '';
        switch ($fillType) {
            case static::DRAW_FILL_AND_STROKE:
                $content .= " B*\n";
                break;
            case static::DRAW_FILL:
                $content .= " f*\n";
                break;
            case static::DRAW_STROKE:
                $content .= " S\n";
                break;
        }
        return $content;
    }

}
