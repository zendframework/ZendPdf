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
    const MODE_FILL = "fill";
    
    /**
     * The REPLACE mode replaces the form field with text using the same position, font, and sizing.
     */
    const MODE_REPLACE = "replace";
    
    private $fieldName;
    
    private $value;
    
    private $mode;
    
    /**
     * Create a new FormToken object, representing a value to be used in this AcroForm.
     * @param string $fieldName the name of the form field that should be affected by this token
     * @param string $value the value to use
     * @param constant $mode one of FormToken::MODE_FILL or FormToken::MODE_REPLACE
     */
    public function __construct($fieldName, $value, $mode) {
        $this->fieldName = $fieldName;
        $this->value = $value;
        if ($mode == self::MODE_FILL || $mode == self::MODE_REPLACE) {
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
     * Returns the supplied mode constant - one of FormToken::MODE_FILL or FormToken::MODE_REPLACE
     * @return constant
     */
    public function getMode()
    {
        return $this->mode;
    }
    
}
