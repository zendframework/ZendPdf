<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Pdf
 */

namespace ZendPdfTest\Color;

use ZendPdf\Color\Html;

/**
 * @category   Zend
 * @package    Zend_PDF
 * @subpackage UnitTests
 * @group      Zend_PDF
 */
class HtmlTest extends \PHPUnit_Framework_TestCase
{
    public function colorProvider()
    {
        return array(
            array('#cccccc', array(0.8)),
            array('#CCCCCC', array(0.8)),
            array('#ffcc66', array(1.0, 0.8, 0.4)),
            array('#FFCC66', array(1.0, 0.8, 0.4)),
            array('#123456', array(0.071, 0.204, 0.337)),

            array('#ccc', array(0.8)),
            array('#CCC', array(0.8)),
            array('#fc6', array(1.0, 0.8, 0.4)),
            array('#FC6', array(1.0, 0.8, 0.4)),
        );
    }

    /**
     * @dataProvider colorProvider
     */
    public function testColor($color, $components)
    {
        $this->assertSame($components, Html::color($color)->getComponents());
    }
}
