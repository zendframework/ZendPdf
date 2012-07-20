<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Pdf
 */

namespace ZendPdf\BinaryParser\Image;

use ZendPdf\BinaryParser;

/**
 * \ZendPdf\Image related file parsers abstract class.
 *
 * @package    Zend_PDF
 * @subpackage Zend_PDF_Image
 */
abstract class AbstractImage extends BinaryParser\AbstractBinaryParser
{
    /**
     * Image Type
     *
     * @var integer
     */
    protected $imageType;

    /**
     * Object constructor.
     *
     * Validates the data source and enables debug logging if so configured.
     *
     * @param \ZendPdf\BinaryParser\DataSource\AbstractDataSource $dataSource
     */
    public function __construct(\ZendPdf\BinaryParser\DataSource\AbstractDataSource $dataSource)
    {
        parent::__construct($dataSource);
        $this->imageType = \ZendPdf\Image::TYPE_UNKNOWN;
    }
}
