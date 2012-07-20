<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Pdf
 */

namespace ZendPdf\Resource\Font\Simple\Standard;

use ZendPdf as Pdf;
use ZendPdf\InternalType;

/**
 * Abstract class definition for the standard 14 Type 1 PDF fonts.
 *
 * The standard 14 PDF fonts are guaranteed to be availble in any PDF viewer
 * implementation. As such, they do not require much data for the font's
 * resource dictionary. The majority of the data provided by subclasses is for
 * the benefit of our own layout code.
 *
 * The standard fonts and the corresponding subclasses that manage them:
 * <ul>
 *  <li>Courier - {@link \ZendPdf\Resource\Font\Simple\Standard\Courier}
 *  <li>Courier-Bold - {@link \ZendPdf\Resource\Font\Simple\Standard\CourierBold}
 *  <li>Courier-Oblique - {@link \ZendPdf\Resource\Font\Simple\Standard\CourierOblique}
 *  <li>Courier-BoldOblique - {@link \ZendPdf\Resource\Font\Simple\Standard\CourierBoldOblique}
 *  <li>Helvetica - {@link \ZendPdf\Resource\Font\Simple\Standard\Helvetica}
 *  <li>Helvetica-Bold - {@link \ZendPdf\Resource\Font\Simple\Standard\HelveticaBold}
 *  <li>Helvetica-Oblique - {@link \ZendPdf\Resource\Font\Simple\Standard\HelveticaOblique}
 *  <li>Helvetica-BoldOblique - {@link \ZendPdf\Resource\Font\Simple\Standard\HelveticaBoldOblique}
 *  <li>Symbol - {@link \ZendPdf\Resource\Font\Simple\Standard\Symbol}
 *  <li>Times - {@link \ZendPdf\Resource\Font\Simple\Standard\Times}
 *  <li>Times-Bold - {@link \ZendPdf\Resource\Font\Simple\Standard\TimesBold}
 *  <li>Times-Italic - {@link \ZendPdf\Resource\Font\Simple\Standard\TimesItalic}
 *  <li>Times-BoldItalic - {@link \ZendPdf\Resource\Font\Simple\Standard\TimesBoldItalic}
 *  <li>ZapfDingbats - {@link \ZendPdf\Resource\Font\Simple\Standard\ZapfDingbats}
 * </ul>
 *
 * Font objects should be normally be obtained from the factory methods
 * {@link \ZendPdf\Font::fontWithName} and {@link \ZendPdf\Font::fontWithPath}.
 *
 * @package    Zend_PDF
 * @subpackage Zend_PDF_Fonts
 */
abstract class AbstractStandard extends \ZendPdf\Resource\Font\Simple\AbstractSimple
{
    /**** Public Interface ****/


    /* Object Lifecycle */

    /**
     * Object constructor
     */
    public function __construct()
    {
        $this->_fontType = Pdf\Font::TYPE_STANDARD;

        parent::__construct();
        $this->_resource->Subtype  = new InternalType\NameObject('Type1');
    }
}
