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

use ZendPdf\Exception\LogicException;
use ZendPdf\InternalType\NumericObject;
use ZendPdf\InternalType\StringObject;
use ZendPdf\Page;

/**
 * Draw a line of text at the specified position.
 *
 * @package    ZendPdf
 * @subpackage ZendPdf\Drawings
 */
class SimpleText extends DrawingAbstract
{
    /**
     *
     * @var string
     */
    protected $text;
    /**
     * @var string
     */
    protected $charEncoding;

    /**
     * Simple text object
     * @param string $text
     * @param string $charEncoding (optional) Character encoding of source text.
     *   Defaults to current locale.
     */
    public function __construct($text, $charEncoding = '')
    {
        $this->text = $text;
        $this->charEncoding = $charEncoding;
    }

    /**
     * Draw a line of text at the specified position.
     * @param Page $page
     * @return string
     * @throws \ZendPdf\Exception\ExceptionInterface
     */
    protected function drawElement(Page $page)
    {
        if ($page->getFont() === null) {
            throw new LogicException('Font has not been set');
        }

        $page->addProcedureSet('Text');

        $textObj = new StringObject($page->getFont()->encodeString($this->text, $this->charEncoding));
        $xCoordinate = new NumericObject($this->xPosition);
        $yCoordinate = new NumericObject($this->yPosition);

        return "BT\n"
        . $xCoordinate->toString() . ' ' . $yCoordinate->toString() . " Td\n"
        . $textObj->toString() . " Tj\n"
        . "ET\n";
    }
}
