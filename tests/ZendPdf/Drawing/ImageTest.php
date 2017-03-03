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
use ZendPdf\Drawings\Image;
use ZendPdf\Page;
use ZendPdf\PdfDocument;
use ZendPdf\Image as FileImage;

/**
 * @category   Zend
 * @package    Zend_PDF
 * @subpackage UnitTests
 * @group      Zend_PDF
 */
class ImageTest extends \PHPUnit_Framework_TestCase
{
    public function testDrawAnImage()
    {
        $pdf = new PdfDocument();
        $page = $pdf->newPage(Page::SIZE_A4);

        $stampImagePNG = FileImage::imageWithPath(__DIR__ . '/../_files/stamp.png');

        $ellipse1 = new Image($stampImagePNG, 300, 550);
        $ellipse1->setPosition(200, 450);
        $actualContent = $ellipse1->draw($page);

        $expected = <<<DRAWING
q
1 0 0 1 200 450 cm
100 0 0 100 0 0 cm
/X1 Do
Q

DRAWING;
        $this->assertEquals($expected, $actualContent);
    }
}
