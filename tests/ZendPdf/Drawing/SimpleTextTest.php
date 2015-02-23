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
use ZendPdf\Color\ColorInterface;
use ZendPdf\Color\Html;
use ZendPdf\Drawings\SimpleText;
use ZendPdf\Font;
use ZendPdf\Page;
use ZendPdf\PdfDocument;
use ZendPdf\Style;

/**
 * @category   Zend
 * @package    Zend_PDF
 * @subpackage UnitTests
 * @group      Zend_PDF
 */
class SimpleTextTest extends \PHPUnit_Framework_TestCase
{
    protected function getPage()
    {
        $pdf = new PdfDocument();
        $page = $pdf->newPage(Page::SIZE_A4);
        $page->setFont(Font::fontWithName(Font::FONT_HELVETICA), 10);
        return $page;
    }

    public function testDrawASimpleText()
    {
        $pdf = new PdfDocument();
        $page = $pdf->newPage(Page::SIZE_A4);
        $page->setFont(Font::fontWithName(Font::FONT_HELVETICA), 10);

        $text = new SimpleText('testing my text');
        $text->setPosition(10, 10);

        $expected = <<<SIMPLETEXT
BT
10 10 Td
(testing my text) Tj
ET

SIMPLETEXT;
        $this->assertEquals($expected, $text->draw($page));
    }

    public function testDrawASimpleTextWithColor()
    {
        $page = $this->getPage();

        $style = new Style();
        $style->setFillColor(Html::color('#FF0000'));

        $text = new SimpleText('testing my text');
        $text->setPosition(10, 10);
        $text->setStyle($style);

        $expected = <<<SIMPLETEXT
1 0 0 rg
BT
10 10 Td
(testing my text) Tj
ET

SIMPLETEXT;
        $this->assertEquals($expected, $text->draw($page));
    }
}
