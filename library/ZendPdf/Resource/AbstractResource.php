<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Pdf
 */

namespace ZendPdf\Resource;

use ZendPdf as Pdf;
use ZendPdf\InternalType;
use ZendPdf\ObjectFactory;

/**
 * PDF file Resource abstraction
 *
 * @package    Zend_PDF
 * @subpackage Zend_PDF_Internal
 */
abstract class AbstractResource
{
    /**
     * Each PDF resource (fonts, images, ...) interacts with a PDF itself.
     * It creates appropriate PDF objects, structures and sometime embedded files.
     * Resources are referenced in content streams by names, which are stored in
     * a page resource dictionaries.
     *
     * Thus, resources must be attached to the PDF.
     *
     * Resource abstraction uses own PDF object factory to store all necessary information.
     * At the render time internal object factory is appended to the global PDF file
     * factory.
     *
     * Resource abstraction also cashes information about rendered PDF files and
     * doesn't duplicate resource description each time then Resource is rendered
     * (referenced).
     *
     * @var \ZendPdf\ObjectFactory
     */
    protected $_objectFactory;

    /**
     * Main resource object
     *
     * @var \ZendPdf\InternalType\IndirectObject
     */
    protected $_resource;

    /**
     * Object constructor.
     *
     * If resource is not a \ZendPdf\InternalType\AbstractTypeObject object,
     * then stream object with specified value is generated.
     *
     * @param \ZendPdf\InternalType\AbstractTypeObject|string $resource
     */
    public function __construct($resource)
    {
        $this->_objectFactory = ObjectFactory::createFactory(1);
        if ($resource instanceof InternalType\AbstractTypeObject) {
            $this->_resource = $this->_objectFactory->newObject($resource);
        } else {
            $this->_resource = $this->_objectFactory->newStreamObject($resource);
        }
    }

    /**
     * Clone page, extract it and dependent objects from the current document,
     * so it can be used within other docs.
     */
    public function __clone()
    {
        $factory = \ZendPdf\ObjectFactory::createFactory(1);
        $processed = array();

        // Clone dictionary object.
        // Do it explicitly to prevent sharing page attributes between different
        // results of clonePage() operation (other resources are still shared)
        $dictionary = new InternalType\DictionaryObject();
        foreach ($this->_pageDictionary->getKeys() as $key) {
            $dictionary->$key = $this->_pageDictionary->$key->makeClone($factory,
                                                                        $processed,
                                                                        InternalType\AbstractTypeObject::CLONE_MODE_SKIP_PAGES);
        }

        $this->_pageDictionary = $factory->newObject($dictionary);
        $this->_objFactory     = $factory;
        $this->_attached       = false;
        $this->_style          = null;
        $this->_font           = null;
    }

    /**
     * Clone page, extract it and dependent objects from the current document,
     * so it can be used within other docs.
     *
     * @internal
     * @param \ZendPdf\ObjectFactory $factory
     * @param array $processed
     * @return \ZendPdf\Page
     */
    public function clonePage($factory, &$processed)
    {
        // Clone dictionary object.
        // Do it explicitly to prevent sharing page attributes between different
        // results of clonePage() operation (other resources are still shared)
        $dictionary = new InternalType\DictionaryObject();
        foreach ($this->_pageDictionary->getKeys() as $key) {
            $dictionary->$key = $this->_pageDictionary->$key->makeClone($factory,
                                                                        $processed,
                                                                        InternalType\AbstractTypeObject::CLONE_MODE_SKIP_PAGES);
        }

        $clonedPage = new Pdf\Page($factory->newObject($dictionary), $factory);
        $clonedPage->_attached = false;

        return $clonedPage;
    }

    /**
     * Get resource.
     * Used to reference resource in an internal PDF data structures (resource dictionaries)
     *
     * @internal
     * @return \ZendPdf\InternalType\IndirectObject
     */
    public function getResource()
    {
        return $this->_resource;
    }

    /**
     * Get factory.
     *
     * @internal
     * @return \ZendPdf\ObjectFactory
     */
    public function getFactory()
    {
        return $this->_objectFactory;
    }
}
