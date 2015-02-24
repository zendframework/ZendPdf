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
use ZendPdf\Color\Cmyk;
use ZendPdf\Color\Html;
use ZendPdf\Color\Rgb;
use ZendPdf\Drawings\Ellipse;
use ZendPdf\Font;
use ZendPdf\Page;
use ZendPdf\PdfDocument;

/**
 * @category   Zend
 * @package    Zend_PDF
 * @subpackage UnitTests
 * @group      Zend_PDF
 */
class EllipseTest extends \PHPUnit_Framework_TestCase
{
    public function testDrawAnEllipse()
    {
        $pdf = new PdfDocument();
        $page = $pdf->newPage(Page::SIZE_A4);

        $ellipse1 = new Ellipse(400, 350);
        $ellipse1->setPosition(250, 400);
        $actualContent = $ellipse1->draw($page);

        $ellipse2 = new Ellipse(400, 350, M_PI / 6, 2 * M_PI / 3);
        $ellipse2->setPosition(250, 400);
        $actualContent .= $ellipse2->draw($page);

        $ellipse3 = new Ellipse(400, 350, -M_PI / 6, M_PI / 6);
        $ellipse3->setPosition(250, 400);
        $actualContent .= $ellipse3->draw($page);


        $expected = <<<SIMPLETEXT
325 400 m
366.4213562373095 400 400 388.80711874576986 400 375 c
400 361.19288125423014 366.4213562373095 350 325 350 c
283.5786437626905 350 250 361.19288125423014 250 375 c
250 388.80711874576986 283.5786437626905 400 325 400 c
 B*
q
325 375 m
454.9038105676658 450 l
363.8228567653782 519.8888739433603 l
250 504.9038105676658 l
h
W
n
325 400 m
366.4213562373095 400 400 388.80711874576986 400 375 c
400 361.19288125423014 366.4213562373095 350 325 350 c
283.5786437626905 350 250 361.19288125423014 250 375 c
250 388.80711874576986 283.5786437626905 400 325 400 c
 B*
Q
q
325 375 m
454.9038105676658 300 l
475 375 l
454.9038105676658 450 l
h
W
n
325 400 m
366.4213562373095 400 400 388.80711874576986 400 375 c
400 361.19288125423014 366.4213562373095 350 325 350 c
283.5786437626905 350 250 361.19288125423014 250 375 c
250 388.80711874576986 283.5786437626905 400 325 400 c
 B*
Q

SIMPLETEXT;
        $this->assertEquals($expected, $actualContent);
    }
}
