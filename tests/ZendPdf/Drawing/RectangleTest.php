<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   ZendPdfTest
 */

namespace ZendPdfTest\Drawing;

/**
 * PHPUnit Test Case
 */
use ZendPdf\Drawings\Rectangle;
use ZendPdf\Font;
use ZendPdf\Page;
use ZendPdf\PdfDocument;

/**
 * @category   Zend
 * @package    Zend_PDF
 * @subpackage UnitTests
 * @group      Zend_PDF
 */
class RectangleTest extends \PHPUnit_Framework_TestCase
{
    protected function getPage()
    {
        $pdf = new PdfDocument();
        $page = $pdf->newPage(Page::SIZE_A4);
        $page->setFont(Font::fontWithName(Font::FONT_HELVETICA), 10);
        return $page;
    }

    public function testDrawARectangle()
    {
        $pdf = new PdfDocument();
        $page = $pdf->newPage(Page::SIZE_A4);
        $page->setFont(Font::fontWithName(Font::FONT_HELVETICA), 10);

        $text = new Rectangle(100, 50);
        $text->setPosition(10, 10);

        $expected = <<<DRAWING
10 10 90 40 re
 B*

DRAWING;
        $this->assertEquals($expected, $text->draw($page));
    }
}
