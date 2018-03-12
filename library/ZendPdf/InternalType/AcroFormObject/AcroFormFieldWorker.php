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
use ZendPdf\InternalType\AcroFormObject\FormToken;

class AcroFormFieldWorker {
    
    /**
     * Determine if the AcroFormObject should process this incoming field or leave it as-is.
     * @param ObjectFactory $targetFactory
     * @param IndirectObject $widget
     * @return boolean TRUE if AcroFormObject should process the field
     */
    public function shouldProcessField(ObjectFactory $targetFactory, IndirectObject $widget)
    {
        if ($widget->FT !== null && $widget->T !== null) {
            return true;
        } elseif ($widget->Parent !== null && $widget->Parent->T !== null) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Replace the existing form field with a read-only text block, using the same text formatting and positioning.
     * @param ObjectFactory $targetFactory
     * @param IndirectObject $widget
     * @param FormToken $token
     */
    public function replaceField(ObjectFactory $targetFactory, IndirectObject $widget, FormToken $token)
    {
        //TODO:
    }
    
    /**
     * Generate the title for this form field. This method allows you to deduplicate or merge
     * form fields, for example while combining multiple PDF files.
     * @param ObjectFactory $targetFactory
     * @param IndirectObject $widget
     * @return string
     */
    public function getTitle(ObjectFactory $targetFactory, IndirectObject $widget)
    {
        if ($widget->FT !== null && $widget->T !== null) {
            $title = $widget->T->value;
        } elseif ($widget->Parent !== null && $widget->Parent->T !== null) {
            $title = $widget->Parent->T->value;
        } else {
            $title = null; // this shouldn't ever be called, unless we're sub-classed... if you subclass this, and change the processField() method, this is YOUR responsibility!
        }
        return $title;
    }
    
    /**
     * Create a new DictionaryObject representing the new shared field.
     * @param ObjectFactory $targetFactory
     * @param IndirectObject $widget
     * @param string $title
     * @param IndirectObject $formDictionary
     * @return IndirectObject
     */
    public function createNewSharedField(ObjectFactory $targetFactory, IndirectObject $widget, $title, IndirectObject $formDictionary)
    {
        $dict = $this->createNewFieldDictionary($widget, $title);
        $objRef = $this->createNewFieldIndirectObject($targetFactory, $dict);
        
        $this->addNewFieldToForm($targetFactory, $objRef, $formDictionary);
        
        return $objRef;
    }
    
    /**
     * @param ObjectFactory $targetFactory
     * @param IndirectObject $widget
     * @param string $title
     * @return DictionaryObject
     */
    protected function createNewFieldDictionary(IndirectObject $widget, $title)
    {
        // NOTE: do not move the value (V) attribute into a shared field dictionary
        // NOTE: isset and property_exists appear to not work very well on the IndirectObject, probably due to
        // the class using a "magic" getter method for the various attributes.
        // NOTE: also make sure you clone the object here, or else it may be retained and reused elsewhere, and
        // not actually end up in the desired shared form field.
        
        // create a new field object
        $dict = new DictionaryObject();
        if ($widget->DA !== null) {
            $dict->DA = clone $widget->DA; // font
        }
        if ($widget->FT !== null) {
            $dict->FT = clone $widget->FT; // field type
        }
        $dict->Kids = new ArrayObject();
        $dict->T = new StringObject($title); // title
        
        $dict->Ff = clone $widget->Ff; // "read-only" setting

        return $dict;
    }
    
    /**
     * @param ObjectFactory $targetFactory
     * @param DictionaryObject $dict
     * @return IndirectObject
     */
    protected function createNewFieldIndirectObject(ObjectFactory $targetFactory, DictionaryObject $dict)
    {
        $objRef = $targetFactory->newObject($dict);
        return $objRef;
    }
    
    /**
     * @param ObjectFactory $targetFactory
     * @param IndirectObject $objRef
     * @param IndirectObject $formDictionary
     */
    protected function addNewFieldToForm(ObjectFactory $targetFactory, IndirectObject $objRef, IndirectObject $formDictionary)
    {
        // add to the form
        $ref = new IndirectObjectReference($objRef->getObjNum(), $objRef->getGenNum(), null, $targetFactory);
        $formDictionary->Fields->items[] = $ref;
    }
    
    /**
     * Update the page-specific field object to point to the new shared field object.
     * @param ObjectFactory $targetFactory
     * @param IndirectObject $pageField
     * @param IndirectObject $sharedField
     */
    public function linkPageFieldToSharedField(ObjectFactory $targetFactory, IndirectObject $pageField, IndirectObject $sharedField)
    {
        // hack up the supplied widget to point to the new shared field
        unset($pageField->FT);
        unset($pageField->T);
        unset($pageField->Ff); // remove the read-only flag
        unset($pageField->P); // TODO: link back to Page object

        // create a new reference for the original embedded field
        $ior = new IndirectObjectReference($sharedField->getObjNum(), $sharedField->getGenNum(), null, $targetFactory); // as long as this IOR references an object in its own factory, the context can be null
        $pageField->Parent = $ior;
        $pageField->getFactory()->markAsModified($pageField);
        
        // add new field usage to the field's Kids array
        $sharedField->Kids->items[] = new IndirectObjectReference($pageField->getObjNum(), $pageField->getGenNum(), null, $pageField->getFactory());
    }
    
}