<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Pdf
 */

namespace ZendPdf\Resource\Font\Simple\Parsed;

use ZendPdf as Pdf;
use ZendPdf\BinaryParser\Font\OpenType as OpenTypeFontParser;
use ZendPdf\InternalType;
use ZendPdf\Resource\Font as FontResource;

/**
 * TrueType fonts implementation
 *
 * Font objects should be normally be obtained from the factory methods
 * {@link \ZendPdf\Font::fontWithName} and {@link \ZendPdf\Font::fontWithPath}.
 *
 * @package    Zend_PDF
 * @subpackage Zend_PDF_Fonts
 */
class TrueType extends AbstractParsed
{
    /**
     * Object constructor
     *
     * @param \ZendPdf\BinaryParser\Font\OpenType\TrueType $fontParser Font parser
     *   object containing parsed TrueType file.
     * @param integer $embeddingOptions Options for font embedding.
     * @throws \ZendPdf\Exception\ExceptionInterface
     */
    public function __construct(OpenTypeFontParser\TrueType $fontParser, $embeddingOptions)
    {
        parent::__construct($fontParser, $embeddingOptions);

        $this->_fontType = Pdf\Font::TYPE_TRUETYPE;

        $this->_resource->Subtype  = new InternalType\NameObject('TrueType');

        $fontDescriptor = FontResource\FontDescriptor::factory($this, $fontParser, $embeddingOptions);
        $this->_resource->FontDescriptor = $this->_objectFactory->newObject($fontDescriptor);
    }
}
