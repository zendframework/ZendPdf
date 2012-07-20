<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Pdf
 */

namespace ZendPdf\InternalStructure;

use ZendPdf as Pdf;
use ZendPdf\Action;
use ZendPdf\Destination;
use ZendPdf\Exception;
use ZendPdf\InternalType;

/**
 * PDF target (action or destination)
 *
 * @package    Zend_PDF
 * @subpackage Zend_PDF_Internal
 */
abstract class NavigationTarget
{
    /**
     * Parse resource and return it as an Action or Explicit Destination
     *
     * $param \ZendPdf\InternalType $resource
     * @return \ZendPdf\Destination\AbstractDestination|\ZendPdf\Action\AbstractAction
     * @throws \ZendPdf\Exception\ExceptionInterface
     */
    public static function load(InternalType\AbstractTypeObject $resource)
    {
        if ($resource->getType() == InternalType\AbstractTypeObject::TYPE_DICTIONARY) {
            if (($resource->Type === null  ||  $resource->Type->value =='Action')  &&  $resource->S !== null) {
                // It's a well-formed action, load it
                return Action\AbstractAction::load($resource);
            } elseif ($resource->D !== null) {
                // It's a destination
                $resource = $resource->D;
            } else {
                throw new Exception\CorruptedPdfException('Wrong resource type.');
            }
        }

        if ($resource->getType() == InternalType\AbstractTypeObject::TYPE_ARRAY  ||
            $resource->getType() == InternalType\AbstractTypeObject::TYPE_NAME   ||
            $resource->getType() == InternalType\AbstractTypeObject::TYPE_STRING) {
            // Resource is an array, just treat it as an explicit destination array
            return Destination\AbstractDestination::load($resource);
        } else {
            throw new Exception\CorruptedPdfException('Wrong resource type.');
        }
    }

    /**
     * Get resource
     *
     * @internal
     * @return \ZendPdf\InternalType\AbstractTypeObject
     */
    abstract public function getResource();
}
