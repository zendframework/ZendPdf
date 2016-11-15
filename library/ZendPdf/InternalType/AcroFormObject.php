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
use ZendPdf\ObjectFactory;
use ZendPdf\InternalType\DictionaryObject;
use ZendPdf\InternalType\IndirectObjectReference;
use ZendPdf\InternalType\IndirectObject;
use ZendPdf\InternalType\ArrayObject;

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
     * Associative array of form fields in this document.
     * @var array of DictionaryObject representing each form field
     */
    protected $_fields = array();

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
    public function __construct($val, ObjectFactory $objFactory)
    {
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
        $worker = $widget->getFactory()->getAcroFormFieldWorker();
        
        if (!$worker->shouldProcessField($factory, $widget)) {
            return;
        }
        
        $title = $worker->getTitle($factory, $widget);
        
        if (array_key_exists($title, $this->_fields)) {
            // reuse the existing field
            $objRef = $this->_fields[$title];
            
        } else {
            // create a new dictionary and indirect object
            $objRef = $worker->createNewSharedField($factory, $widget, $title, $this->_primaryFormDict);
            
            $this->_fields[$title] = $objRef;
        }
        
        $worker->linkPageFieldToSharedField($factory, $widget, $objRef);
    }
    
    protected function mergeAndDestroyForm(ObjectFactory $factory, $key, DictionaryObject $dict)
    {
        throw new \Exception("TODO: merge AcroForm dictionaries");
    }
    
}