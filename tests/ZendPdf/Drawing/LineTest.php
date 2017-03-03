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
use ZendPdf\Drawings\Line;
use ZendPdf\Page;
use ZendPdf\PdfDocument;

/**
 * @category   Zend
 * @package    Zend_PDF
 * @subpackage UnitTests
 * @group      Zend_PDF
 */
class LineTest extends \PHPUnit_Framework_TestCase
{
    public function testDrawALine()
    {
        $pdf = new PdfDocument();
        $page = $pdf->newPage(Page::SIZE_A4);

        $ellipse1 = new Line(300, 550);
        $ellipse1->setPosition(200, 450);
        $actualContent = $ellipse1->draw($page);

        $expected = <<<DRAWING
200 450 m
300 550 l
 S

DRAWING;
        $this->assertEquals($expected, $actualContent);
    }
}
