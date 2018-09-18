<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2016 RightSource Compliance
 * @author    Nate Chrysler <nchrysler@rightsourcecompliance.com>
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Pdf
 */

namespace ZendPdf\InternalType\AcroFormObject;

use ZendPdf\ObjectFactory;
use ZendPdf\InternalType\IndirectObject;
use ZendPdf\InternalType\IndirectObjectReference;
use ZendPdf\InternalType\DictionaryObject;
use ZendPdf\InternalType\ArrayObject;
use ZendPdf\InternalType\StringObject;

class FormToken {
    
    /**
     * The FILL mode pre-populates the form field with the requested value.
     */
//    const MODE_FILL = "fill";
    
    /**
     * The REPLACE mode replaces the form field with text using the same position, font, and sizing.
     */
    const MODE_REPLACE = "replace";//won't wrap text but will go down a line if \n is provided in the string
    const MODE_REPLACE_WRAP = "replace_wrap";//will wrap text either at edge of token field or at any \n provided
    
    private $fieldName;
    
    private $value;
    
    private $mode;
    
    private $offsetX = 0;
    
    private $offsetY = 0;
    
    /**
     * Create a new FormToken object, representing a value to be used in this AcroForm.
     * @param string $fieldName the name of the form field that should be affected by this token
     * @param string $value the value to use
     * @param constant $mode one of FormToken::MODE_* constants
     * @param int $offsetX
     * @param int $offsetY
     */
    public function __construct($fieldName, $value, $mode, $offsetX=0, $offsetY=0) {
        $this->fieldName = $fieldName;
        $this->value = $value;
        $this->offsetX = $offsetX;
        $this->offsetY = $offsetY;
        
        if ($mode == self::MODE_REPLACE || $mode == self::MODE_REPLACE_WRAP) { // $mode == self::MODE_FILL || 
            $this->mode = $mode;
        } else {
            throw new \ZendPdf\Exception\NotImplementedException("Unknown mode supplied: " . $mode);
        }
    }
    
    /**
     * Returns the supplied field name.
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }
    
    /**
     * Returns the supplied value.
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * Returns the supplied mode constant - one of FormToken::MODE_* constants
     * @return constant
     */
    public function getMode()
    {
        return $this->mode;
    }
    
    /**
     * When replacing the form field with read-only text, use this offset for positioning the new text
     * @return integer
     */
    public function getOffsetX()
    {
        return $this->offsetX;
    }
    
    /**
     * When replacing the form field with read-only text, use this offset for positioning the new text
     * @return integer
     */
    public function getOffsetY()
    {
        return $this->offsetY;
    }
    
}
