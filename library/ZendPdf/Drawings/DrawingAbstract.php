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
 * Drawing Abstract
 *
 * @package    ZendPdf
 * @subpackage ZendPdf\Drawings
 */
abstract class DrawingAbstract implements DrawingInterface
{
    /**
     * @var float
     */
    protected $xPosition;
    /**
     * @var float
     */
    protected $yPosition;
    /**
     * @var Style
     */
    protected $style;

    /**
     * @inheritdoc
     */
    public function setPosition($x, $y)
    {
        $this->setX($x);
        $this->setY($y);
    }

    /**
     * @inheritdoc
     */
    public function setX($position)
    {
        $this->xPosition = (float)$position;
    }

    /**
     * @inheritdoc
     */
    public function setY($position)
    {
        $this->yPosition = (float)$position;
    }

    /**
     * @inheritdoc
     */
    public function setStyle(Style $style)
    {
        $this->style = $style;
        return $this;
    }

    /**
     * @inheritdoc
     */
    final public function draw(Page $page)
    {
        $content = $this->drawStyle($page);
        $content .= $this->drawElement($page);
        return $content;
    }

    /**
     * Draw element with Pdf elements.
     * @param Page $page
     * @return string
     * @throws \ZendPdf\Exception\ExceptionInterface
     */
    abstract protected function drawElement(Page $page);

    /**
     * Draw style with Pdf elements.
     * @param Page $page
     * @return string
     */
    protected function drawStyle(Page $page)
    {
        if ($this->style == null) {
            return '';
        }
        $page->addProcedureSet('Text');
        $page->addProcedureSet('PDF');
        if ($this->style->getFont() !== null) {
            $page->setFont($this->style->getFont(), $this->style->getFontSize());
        }
        return $this->style->instructions();
    }
}
