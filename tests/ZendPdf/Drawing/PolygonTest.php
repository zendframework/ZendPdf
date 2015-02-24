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
use ZendPdf\Drawings\Polygon;
use ZendPdf\Page;
use ZendPdf\PdfDocument;

/**
 * @category   Zend
 * @package    Zend_PDF
 * @subpackage UnitTests
 * @group      Zend_PDF
 */
class PolygonTest extends \PHPUnit_Framework_TestCase
{
    public function testDrawALine()
    {
        $pdf = new PdfDocument();
        $page = $pdf->newPage(Page::SIZE_A4);

        $x = array();
        $y = array();
        for ($count = 0; $count < 8; $count++) {
            $x[] = 140 + 25*cos(3*M_PI_4*$count);
            $y[] = 375 + 25*sin(3*M_PI_4*$count);
        }
        $polygon = new Polygon($x, $y, Polygon::DRAW_FILL_AND_STROKE, Polygon::FILL_METHOD_EVEN_ODD);

        $expected = <<<DRAWING
165 375 m
122.32233047033631 392.6776695296637 l
140 350 l
157.6776695296637 392.6776695296637 l
115 375 l
157.6776695296637 357.3223304703363 l
140 400 l
122.32233047033631 357.3223304703363 l
 b*

DRAWING;
        $this->assertEquals($expected, $polygon->draw($page));
    }
}
