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

namespace ZendPdf\InternalType;

use ZendPdf as Pdf;
use ZendPdf\Exception;
use ZendPdf\Page;
use ZendPdf\Font;
use ZendPdf\ObjectFactory;
use ZendPdf\InternalType\DictionaryObject;
use ZendPdf\InternalType\IndirectObjectReference;
use ZendPdf\InternalType\IndirectObject;
use ZendPdf\InternalType\ArrayObject;
use ZendPdf\InternalType\AcroFormObject\FormToken;

/**
 * PDF file 'AcroForm' element implementation
 *
 * @category   Zend
 * @package    Zend_PDF
 * @subpackage Zend_PDF_Internal
 */
class AcroFormObject
{
    
    /**
     * The owning PDF Document.
     * @var Pdf\PdfDocument
     */
    protected $_pdf;
    
    /**
     * Associative array of form fields in this document.
     * @var array of IndirectObject representing each form field
     */
    protected $_fields = array();
    
    /**
     * Associative array of form tokens to be used when rendering.
     * @var array of FormToken objects
     */
    protected $_tokens = array();

    /**
     * PDF objects factory.
     *
     * @var \ZendPdf\ObjectFactory
     */
    protected $_objFactory = null;
    
    /**
     * Array of object factories already processed by this form
     * @var array where the key is the ObjectFactory->getId() and the value is the ObjectFactory itself
     */
//    protected $objFactories = array();
    
    /**
     * Reference to the primary form DictionaryObject (wrapped in an IndirectObject)
     * @var IndirectObject
     */
    protected $_primaryFormDict = null;
    
    /**
     * The original form object supplied to the constructor
     * @var AbstractTypeObject
     */
    protected $_sourceForm = null;
    
    /**
     * Reference to the context extracted from the primary form
     * @var IndirectObjectReference\Context
     */
    protected $_primaryContext = null;
    
    /**
     * Array of IndirectObjectReference that each point to an AcroForm DictionaryObject
     * @var array of IndirectObjectReference objects
     */
    public $_formObjReferences = null;
    
    /**
     * A log of events related to processing the form
     * @var array
     */
    protected $log = array();
    
    /**
     * The parent form when merging pages from multiple PDFs
     * @var AcroFormObject
     */
    protected $_parentForm = null;
    
    /**
     * Object constructor
     *
     * @param IndirectObjectReference $val that points to an IndirectObject, and in turn a DictionaryObject
     * @param ObjectFactory $objFactory
     * @throws \ZendPdf\Exception\ExceptionInterface
     */
    public function __construct(Pdf\PdfDocument $pdf, $val, ObjectFactory $objFactory)
    {
        $this->_pdf = $pdf;
        $this->_sourceForm = $val;
        $this->_objFactory = $objFactory;
        
        $this->_formObjReferences = [];
        
        // find the IndirectObject that contains the DictionaryObject
        if ($val !== null) {
            $formDict = $val->getObject();
        } else {
            $formDict = null;
        }
        $this->createForm($formDict, $objFactory);
    }
    
    /**
     * Create a shared form field object for each source form field in the source form. Note that this
     * should only be called in context of the parent AcroFormObject when there are multiple forms/pages
     * being merged.
     * @param AcroFormObject $formObject or null if it should use $this
     */
    public function processFormFields($formObject = null)
    {
        if ($formObject === null) {
            $formObject = $this;
        }
        
        $this->processFormFieldsInFactory($formObject, $this->_objFactory);
        
    }
    
    /**
     * 
     * @param \ZendPdf\InternalType\AcroFormObject $formObject
     * @param ObjectFactory $factory
     */
    private function processFormFieldsInFactory(AcroFormObject $formObject, ObjectFactory $factory)
    {
        $fieldItems = $factory->getModifiedObjects();
        
        // catalog the form fields
        foreach ($fieldItems as $io) {
            if ($io instanceof IndirectObject) {
                /* @var $io IndirectObject */
                if ($io->getType() == IndirectObject::TYPE_DICTIONARY && $this->isFormField($io)) {
                    $formObject->createFormField($io, $formObject->getObjFactory());
                }
            }
        }
        
        foreach ($factory->getAttachedFactories() as $subFactory) {
            $this->processFormFieldsInFactory($formObject, $subFactory);
        }
    }
    
    /**
     * Process the supplied FormToken objects to replace form fields with read-only values.
     * @param array $pages array of Page objects in the current document
     * @param AcroFormObject $formObject or null if it should use $this
     */
    public function replaceTokens($pages)
    {
        // loop through supplied tokens, find existing form fields, find and replace the field's instances with text blocks, delete the field references and any pointers in the ObjectFactory
        /* @var $token FormToken */
        /* @var $field IndirectObject */
        foreach ($this->_tokens as $token) {
            $fieldName = $token->getFieldName();
            if (array_key_exists($fieldName, $this->_fields)) {
                $field = $this->_fields[$fieldName];
                
                // the Kids property contains references to field instances, and each field instance's Parent property refers to the shared field
                if ($field->Kids instanceof ArrayObject) {
                    /* @var $idr IndirectObjectReference */
                    $i=0; 
                    /* @var $items \ArrayObject */
                    $items = $field->Kids->items;
                    foreach ($items as $idr) {
                        $io = $idr->getObject();
                        /*
                         * Source properties that will be needed:
                         * DA = text style
                         * Rect = positioning
                         * P = page (note it's not always available - why?)
                         * Options for text block:
                         * - get the page, call drawText()?
                         * - repliace what happens in drawText()?
                         */
                        $da = $idr->DA; // example: "/TiRo 8 Tf 0 g"
                        $p = $idr->P;
                        $this->log[] = "processReplaceTokens(): Retrieved the field instance data";
                        
                        if ($p === null) {
                            // we gotta go find the page now...
                            /* @var $page Page */
                            foreach ($pages as $page) {
                                if ($page->findAnnotation($io)) {
                                    $p = $page;
                                    break;
                                }
                            }
                        }
                        if ($p !== null) {
                            /* @var $p Page */
                            // draw some text!
                            list($font, $size) = $this->getFontAndSize($da);
                            
                            $p->setFont($font, $size);
                            $p->drawTextAt($token->getValue(), $io, $token->getOffsetX(), $token->getOffsetY());
                        }
                        
                        $io->getFactory()->remove($io);
                    }
                    
                    // remove all the field instances - empty the array
                    $field->Kids->items = new \ArrayObject();
                }
                
                // remove the field from its factory
                $field->getFactory()->remove($field);
                
            }
        }
    }
    
    /**
     * Extract the font styling from the supplied string.
     * @param string $da Font styling string (e.g. Helv 12 Tf 0 g) typically found in the DA attribute on a PDF element.
     * @return list($font, $size, $g)
     */
    private function getFontAndSize($da)
    {
//        $fonts = $this->_pdf->extractFonts();
        
        $font = null;
        // parse font information from DA
        $reg = '/^\(\\/(.*?) ([0-9]+) Tf ([0-9]+) g\)$/';
        $matches = [];
        
        $da_str = ($da === null) ? "" : $da->toString();
        $reg_result = preg_match($reg, $da_str, $matches);
        if ($reg_result == 1) {
            // get the font size
            $fontName = $matches[1];
            // TODO: properly look up font names. E.g. $fontName might be "TiRo", and there is an
            // xref SOMEWHERE that we can use that looks like this: <</BaseFont/Helvetica/Encoding 4 0 R/Name/Helv/Subtype/Type1/Type/Font>>
            $font = $this->_pdf->extractFont($fontName);
            $size = intval($matches[2]);
            $g = intval($matches[3]);
        } else {
            // defaults
            $size = 10;
            $g = 0;
        }
        if ($font === null) {
            $font = new \ZendPdf\Resource\Font\Simple\Standard\TimesRoman();
        }
        return [$font, $size, $g];
    }
    
    /**
     * 
     * @param IndirectObject $obj
     * @return boolean
     */
    private function isFormField(IndirectObject $obj)
    {
        if ($obj->Type !== null && $obj->Type->value === "Annot" && $obj->Subtype !== null && $obj->Subtype->value === "Widget") {
            return true;
        } else {
            return false;
        }
    }
    
    public function getObjFactory()
    {
        return $this->_objFactory;
    }
    
    /**
     * Adds an AcroForm parameter to the Root object, if this form contains any defined fields
     * @param AbstractTypeObject the Root object
     * @return IndirectObjectReference
     */
    public function createFormReference(AbstractTypeObject $root)
    {
        /* @var $fields ArrayObject */
        $fields = $this->_primaryFormDict->Fields;
        if (count($fields->items) > 0) {
            $ref = new IndirectObjectReference($this->_primaryFormDict->getObjNum(), $this->_primaryFormDict->getGenNum(), null, $this->_objFactory);
            $root->AcroForm = $ref;
        }
    }
    
    public function merge(AcroFormObject $otherForm)
    {
        foreach ($otherForm->_formObjReferences as $ref)
        {
            if (!in_array($ref, $this->_formObjReferences, true)) {
                /* @var $ref IndirectObjectReference */
                $this->_formObjReferences[] = $ref;
            }
        }
    }
    
    /**
     * Add (or replace) a token.
     * @param FormToken $token
     */
    public function addToken(FormToken $token)
    {
        $this->_tokens[$token->getFieldName()] = $token;
    }
    
    /**
     * Remove an existing token from the array of tokens.
     * @param string $tokenFieldName
     */
    public function removeToken($tokenFieldName)
    {
        unset($this->_tokens[$tokenFieldName]);
    }
    
    /**
     * Override any current tokens and set all the tokens supplied by the array. Can be an indexed or associative array, as long as each value is a FormToken object.
     * @param array $tokens array of FormToken objects
     */
    public function setTokens($tokens)
    {
        // start with a blank array
        $this->_tokens = array();
        
        // add each supplied token
        foreach ($tokens as $token)
        {
            if ($token instanceof FormToken) {
                $this->addToken($token);
            }
        }
    }
    
    /**
     * 
     * @param IndirectObject $sourceForm
     * @param ObjectFactory $factory
     */
    protected function createForm($sourceForm, ObjectFactory $factory)
    {
        // create a new field object
        $dict = new DictionaryObject();
        $dict->Fields = new ArrayObject();
        
        // copy font configuration
        if ($sourceForm !== null && $sourceForm instanceof IndirectObject) {
            if ($sourceForm->DA !== null) {
                $dict->DA = clone $sourceForm->DA;
            }
            if ($sourceForm->DR !== null) {
                $dict->DR = clone $sourceForm->DR;
            }
            if ($sourceForm->Font !== null) {
                $dict->Font = clone $sourceForm->Font;
            }
        }
        
        // create a shared field object
        $objRef = $factory->newObject($dict);

        $this->_primaryFormDict = $objRef;
    }
    
    /**
     * Create a new form field OR locate an existing one by the same name.
     * @param IndirectObject $widget
     * @param ObjectFactory $factory the object factory in which to create any NEW objects (NOTE: this is NOT necessarily the object factory that contains $widget)
     * @return IndirectObjectReference a reference to the shared form field
     */
    protected function createFormField(IndirectObject $widget, ObjectFactory $factory)
    {
        /* @var $token FormToken */
        $worker = $widget->getFactory()->getAcroFormFieldWorker();
        $title = $worker->getTitle($factory, $widget);
        $token = (array_key_exists($title, $this->_tokens)) ? $this->_tokens[$title] : null;
        
        // if this field has already been converted to a shared field, leave it be
        if (!$worker->shouldProcessField($factory, $widget)) {
            return;
        }
        
        // set up the shared form field object
        if (array_key_exists($title, $this->_fields)) {
            // reuse the existing field
            $objRef = $this->_fields[$title];
            
        } else {
            // create a new dictionary and indirect object
            $objRef = $worker->createNewSharedField($factory, $widget, $title, $this->_primaryFormDict);
            
            $this->_fields[$title] = $objRef;
        }
        
        // populate the default value
        // note: FormToken:MODE_REPLACE is handled separately, after the form fields are merged. @see replaceTokens()
//        if ($token !== null && $token->getMode() == FormToken::MODE_FILL) {
//            // apply the value to both the original field and the shared field
//            $widget->V = new StringObject($token->getValue());
//            $objRef->V = new StringObject($token->getValue());
//        }
        
        $worker->linkPageFieldToSharedField($factory, $widget, $objRef);
    }
    
    protected function mergeAndDestroyForm(ObjectFactory $factory, $key, DictionaryObject $dict)
    {
        throw new \Exception("TODO: merge AcroForm dictionaries");
    }
    
}