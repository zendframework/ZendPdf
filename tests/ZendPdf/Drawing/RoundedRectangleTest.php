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
use ZendPdf\Drawings\RoundedRectangle;
use ZendPdf\Font;
use ZendPdf\Page;
use ZendPdf\PdfDocument;

/**
 * @category   Zend
 * @package    Zend_PDF
 * @subpackage UnitTests
 * @group      Zend_PDF
 */
class RoundedRectangleTest extends \PHPUnit_Framework_TestCase
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

        $text = new RoundedRectangle(100, 50, 10);
        $text->setPosition(10, 10);

        $expected = <<<SIMPLETEXT
20 50 m
90 50 l
100 50 100 50 100 40  c
100 20 l
100 10 100 10 90 10  c
20 10 l
10 10 10 10 10 20  c
10 40 l
10 50 10 50 20 50  c
 B*

SIMPLETEXT;
        $this->assertEquals($expected, $text->draw($page));
    }
}
