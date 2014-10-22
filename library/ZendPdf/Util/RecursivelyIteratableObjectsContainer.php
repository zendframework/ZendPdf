<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Pdf
 */

namespace ZendPdf\Util;

/**
 * Iteratable objects container
 *
 * @package    Zend_PDF
 * @subpackage Zend_PDF_Util
 * @deprecated Use RecursivelyIterableObjectsContainer instead
 */
class RecursivelyIteratableObjectsContainer implements \RecursiveIterator, \Countable
{
    protected $_objects = array();

    /** @deprecated use RecursivelyIterableObjectsContainer instead */
    public function __construct(array $objects) { $this->_objects = $objects; }

    /** @deprecated use RecursivelyIterableObjectsContainer instead */
    public function current()      { return current($this->_objects);            }

    /** @deprecated use RecursivelyIterableObjectsContainer instead */
    public function key()          { return key($this->_objects);                }

    /** @deprecated use RecursivelyIterableObjectsContainer instead */
    public function next()         { return next($this->_objects);               }
    
    /** @deprecated use RecursivelyIterableObjectsContainer instead */
    public function rewind()       { return reset($this->_objects);              }

    /** @deprecated use RecursivelyIterableObjectsContainer instead */
    public function valid()        { return current($this->_objects) !== false;  }
    
    /** @deprecated use RecursivelyIterableObjectsContainer instead */
    public function getChildren()  { return current($this->_objects);            }

    /** @deprecated use RecursivelyIterableObjectsContainer instead */
    public function hasChildren()  { return count($this->_objects) > 0;          }

    /** @deprecated use RecursivelyIterableObjectsContainer instead */
    public function count() { return count($this->_objects); }
}
