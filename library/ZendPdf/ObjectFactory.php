<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Pdf
 */

namespace ZendPdf;

use ZendPdf as Pdf;
use ZendPdf\Exception;
use ZendPdf\InternalType;
use ZendPdf\ObjectFactory\UpdateInfoContainer;
use ZendPdf\InternalType\AcroFormObject\AcroFormFieldWorker;

/**
 * PDF element factory.
 * Responsibility is to log PDF changes
 *
 * @package    Zend_PDF
 * @subpackage Zend_PDF_Internal
 */
class ObjectFactory
{
    /**
     * List of the modified objects.
     * Also contains new and removed objects
     *
     * Array: ojbectNumber => \ZendPdf\InternalType\IndirectObject
     *
     * @var array
     */
    private $_modifiedObjects = array();

    /**
     * List of the removed objects
     *
     * Array: ojbectNumber => \ZendPdf\InternalType\IndirectObject
     *
     * @var SplObjectStorage
     */
    private $_removedObjects;

    /**
     * List of registered objects.
     * Used for resources clean up when factory is destroyed.
     *
     * Array of \ZendPdf\InternalType\AbstractTypeObject objects
     *
     * @var array
     */
    private $_registeredObjects = array();

    /**
     * PDF object counter.
     * Actually it's an object number for new PDF object
     *
     * @var integer
     */
    private $_objectCount;


    /**
     * List of the attached object factories.
     * Array of \ZendPdf\ObjectFactory objects
     *
     * @var array
     */
    private $_attachedFactories = array();


    /**
     * Factory internal id
     *
     * @var integer
     */
    private $_factoryId;

    /**
     * Identity, used for factory id generation
     *
     * @var integer
     */
    private static $_identity = 0;


    /**
     * Internal cache to save calculated shifts
     *
     * @var array
     */
    private $_shiftCalculationCache = array();
    
    /**
     * Suffix to append to any AcroForm fields found in this ObjectFactory
     * @var AcroFormFieldWorker
     */
    private $_acroFormFieldWorker = null;

    
    /**
     * Object constructor
     *
     * @param integer $objCount
     */
    public function __construct($objCount)
    {
        $this->_objectCount    = (int)$objCount;
        $this->_factoryId      = self::$_identity++;
        $this->_removedObjects = new \SplObjectStorage();
    }


    /**
     * Factory generator
     *
     * @param integer $objCount
     * @return \ZendPdf\ObjectFactory
     */
    public static function createFactory($objCount)
    {
        return new self($objCount);
    }

    /**
     * Close factory and clean-up resources
     *
     * @internal
     */
    public function close()
    {
        $this->_modifiedObjects   = null;
        $this->_removedObjects    = null;
        $this->_attachedFactories = null;

        foreach ($this->_registeredObjects as $obj) {
            $obj->cleanUp();
        }
        $this->_registeredObjects = null;
    }
    
    /**
     * @param AcroFormFieldWorker $worker
     */
    public function setAcroFormFieldWorker(AcroFormFieldWorker $worker)
    {
        $this->_acroFormFieldWorker = $worker;
    }
    
    /**
     * @return AcroFormFieldWorker
     */
    public function getAcroFormFieldWorker()
    {
        if ($this->_acroFormFieldWorker === null) {
            $this->_acroFormFieldWorker = new AcroFormFieldWorker();
        }
        return $this->_acroFormFieldWorker;
    }

    /**
     * Get factory ID
     *
     * @return integer
     */
    public function getId()
    {
        return $this->_factoryId;
    }

    /**
     * Set object counter
     *
     * @param integer $objCount
     */
    public function setObjectCount($objCount)
    {
        $this->_objectCount = (int)$objCount;
    }

    /**
     * Get object counter
     *
     * @return integer
     */
    public function getObjectCount()
    {
        $count = $this->_objectCount;

        foreach ($this->_attachedFactories as $attached) {
            $count += $attached->getObjectCount() - 1; // -1 as "0" object is a special case and shared between factories
        }

        return $count;
    }


    /**
     * Attach factory to the current;
     *
     * @param \ZendPdf\ObjectFactory $factory
     */
    public function attach(ObjectFactory $factory)
    {
        if ( $factory === $this || isset($this->_attachedFactories[$factory->getId()])) {
            /**
             * Don't attach factory twice.
             * We do not check recusively because of nature of attach operation
             * (Pages are always attached to the Documents, Fonts are always attached
             * to the pages even if pages already use Document level object factory and so on)
             */
            return;
        }
        
        $this->_attachedFactories[$factory->getId()] = $factory;
    }
    


    /**
     * Calculate object enumeration shift.
     *
     * @param \ZendPdf\ObjectFactory $factory
     * @return integer
     */
    public function calculateShift(ObjectFactory $factory)
    {
        if ($factory === $this) {
            return 0;
        }

        if (isset($this->_shiftCalculationCache[$factory->_factoryId])) {
            return $this->_shiftCalculationCache[$factory->_factoryId];
        }
        
        // determine our shift based on attached sub-factories
        $shift = $this->_objectCount - 1;

        foreach ($this->_attachedFactories as $subFactory) {
            $subFactoryShift = $subFactory->calculateShift($factory);

            if ($subFactoryShift != -1) {
                // context found
                $this->_shiftCalculationCache[$factory->_factoryId] = $shift + $subFactoryShift;
                return $shift + $subFactoryShift;
            } else {
                $shift += $subFactory->getObjectCount()-1;
            }
        }

        $this->_shiftCalculationCache[$factory->_factoryId] = -1;
        return -1;
    }

    /**
     * Clean enumeration shift cache.
     * Has to be used after PDF render operation to let followed updates be correct.
     */
    public function cleanEnumerationShiftCache()
    {
        $this->_shiftCalculationCache = array();

        foreach ($this->_attachedFactories as $attached) {
            $attached->cleanEnumerationShiftCache();
        }
    }

    /**
     * Retrive object enumeration shift.
     *
     * @param \ZendPdf\ObjectFactory $factory
     * @return integer
     * @throws \ZendPdf\Exception\ExceptionInterface
     */
    public function getEnumerationShift(ObjectFactory $factory)
    {
        if (($shift = $this->calculateShift($factory)) == -1) {
            throw new Exception\RuntimeException('Wrong object context');
        }

        return $shift;
    }

    /**
     * Mark object as modified in context of current factory.
     *
     * @param \ZendPdf\InternalType\IndirectObject $obj
     * @throws \ZendPdf\Exception\ExceptionInterface
     */
    public function markAsModified(InternalType\IndirectObject $obj)
    {
        if ($obj->getFactory() !== $this) {
            throw new Exception\RuntimeException('Object is not generated by this factory');
        }

        $this->_modifiedObjects[$obj->getObjNum()] = $obj;
    }


    /**
     * Remove object in context of current factory.
     *
     * @param \ZendPdf\InternalType\IndirectObject $obj
     * @throws \ZendPdf\Exception\ExceptionInterface
     */
    public function remove(InternalType\IndirectObject $obj)
    {
        if (!$obj->compareFactory($this)) {
            throw new Exception\RuntimeException('Object is not generated by this factory');
        }

        $this->_modifiedObjects[$obj->getObjNum()] = $obj;
        $this->_removedObjects->attach($obj);
    }


    /**
     * Generate new \ZendPdf\InternalType\IndirectObject
     *
     * @todo Reusage of the freed object. It's not a support of new feature, but only improvement.
     *
     * @param \ZendPdf\InternalType\AbstractTypeObject $objectValue
     * @return \ZendPdf\InternalType\IndirectObject
     */
    public function newObject(InternalType\AbstractTypeObject $objectValue)
    {
        $obj = new InternalType\IndirectObject($objectValue, $this->_objectCount++, 0, $this);
        $this->_modifiedObjects[$obj->getObjNum()] = $obj;
        return $obj;
    }

    /**
     * Generate new \ZendPdf\InternalType\StreamObject
     *
     * @todo Reusage of the freed object. It's not a support of new feature, but only improvement.
     *
     * @param mixed $objectValue
     * @return \ZendPdf\InternalType\StreamObject
     */
    public function newStreamObject($streamValue)
    {
        $obj = new InternalType\StreamObject($streamValue, $this->_objectCount++, 0, $this);
        $this->_modifiedObjects[$obj->getObjNum()] = $obj;
        return $obj;
    }


    /**
     * Enumerate modified objects.
     * Returns array of \ZendPdf\ObjectFactory\UpdateInfoContainer
     *
     * @param \ZendPdf\ObjectFactory $rootFactory
     * @return array
     */
    public function listModifiedObjects($rootFactory = null)
    {
        if ($rootFactory == null) {
            $rootFactory = $this;
            $shift = 0;
        } else {
            $shift = $rootFactory->getEnumerationShift($this);
        }

        ksort($this->_modifiedObjects);

        $result = array();
        foreach ($this->_modifiedObjects as $objNum => $obj) {
            if ($this->_removedObjects->contains($obj)) {
                            $result[$objNum+$shift] = new UpdateInfoContainer($objNum + $shift,
                                                                              $obj->getGenNum()+1,
                                                                              true);
            } else {
                $result[$objNum+$shift] = new UpdateInfoContainer($objNum + $shift,
                                                                  $obj->getGenNum(),
                                                                  false,
                                                                  $obj->dump($rootFactory));
            }
        }

        foreach ($this->_attachedFactories as $factory) {
            $result += $factory->listModifiedObjects($rootFactory);
        }

        return $result;
    }

    /**
     * Register object in the factory
     *
     * It's used to clear "parent object" referencies when factory is closed and clean up resources
     *
     * @param string $refString
     * @param \ZendPdf\InternalType\IndirectObject $obj
     */
    public function registerObject(InternalType\IndirectObject $obj, $refString)
    {
        $this->_registeredObjects[$refString] = $obj;
    }

    /**
     * Fetch object specified by reference
     *
     * @param string $refString
     * @return \ZendPdf\InternalType\IndirectObject|null
     */
    public function fetchObject($refString)
    {
        if (!isset($this->_registeredObjects[$refString])) {
            return null;
        }
        return $this->_registeredObjects[$refString];
    }
    
    /**
     * Fetch all the modified objects in an associative array
     * @return array
     */
    public function getModifiedObjects()
    {
        return $this->_modifiedObjects;
    }
    
    /**
     * Return the attached ObjectFactory objects
     */
    public function getAttachedFactories()
    {
        return $this->_attachedFactories;
    }

    /**
     * Check if PDF file was modified
     *
     * @return boolean
     */
    public function isModified()
    {
        if (count($this->_modifiedObjects) != 0) {
            return true;
        }

        foreach ($this->_attachedFactories as $subFactory) {
            if ($subFactory->isModified()) {
                return true;
            }
        }

        return false;
    }
}
