<?php

namespace IPP\Student\Runtime\BuiltIn;

use IPP\Student\Exception\DoNotUnderstandException;
use IPP\Student\Runtime\Environment;
use IPP\Student\Runtime\ObjectFactory;
use IPP\Student\Runtime\SOL\SOLClass;
use IPP\Student\Runtime\SOL\SOLObject;

/**
 * The Object class implementation
 */
class ObjectClass
{
    private SOLClass $objectClass;
    
    /**
     * Create and initialize the Object class
     * 
     * @param Environment $env Runtime environment
     * @param ObjectFactory $factory Object factory
     * @return SOLClass The Object class
     */
    public function create(Environment $env, ObjectFactory $factory): SOLClass
    {
        // Create the Object class (it has no parent)
        $this->objectClass = new SOLClass('Object', null);
        
        // Register built-in methods
        $this->registerBuiltInMethods($env, $factory);
        
        return $this->objectClass;
    }
    
    /**
     * Register built-in methods for the Object class
     * 
     * @param Environment $env Runtime environment
     * @param ObjectFactory $factory Object factory
     */
    private function registerBuiltInMethods(Environment $env, ObjectFactory $factory): void
    {
        // Common methods for all objects
        
        // identicalTo:
        $this->registerMethod('identicalTo:', function(SOLObject $receiver, array $arguments) use ($factory): SOLObject {
            if (count($arguments) !== 1) {
                throw new DoNotUnderstandException('Method identicalTo: expected 1 argument');
            }
            
            $other = $arguments[0];
            $result = $receiver === $other;
            
            return $result ? $factory->getTrue() : $factory->getFalse();
        });
        
        // equalTo:
        $this->registerMethod('equalTo:', function(SOLObject $receiver, array $arguments) use ($factory): SOLObject {
            if (count($arguments) !== 1) {
                throw new DoNotUnderstandException('Method equalTo: expected 1 argument');
            }
            
            $other = $arguments[0];
            
            // Default implementation: use identicalTo:
            $result = $receiver === $other;
            
            return $result ? $factory->getTrue() : $factory->getFalse();
        });
        
        // asString
        $this->registerMethod('asString', function(SOLObject $receiver, array $arguments) use ($factory): SOLObject {
            if (count($arguments) !== 0) {
                throw new DoNotUnderstandException('Method asString expected 0 arguments');
            }
            
            // Default implementation: return empty string
            return $factory->createString('');
        });
        
        // type checking methods
        $this->registerMethod('isNumber', function(SOLObject $receiver, array $arguments) use ($factory): SOLObject {
            return $factory->getFalse();
        });
        
        $this->registerMethod('isString', function(SOLObject $receiver, array $arguments) use ($factory): SOLObject {
            return $factory->getFalse();
        });
        
        $this->registerMethod('isBlock', function(SOLObject $receiver, array $arguments) use ($factory): SOLObject {
            return $factory->getFalse();
        });
        
        $this->registerMethod('isNil', function(SOLObject $receiver, array $arguments) use ($factory): SOLObject {
            return $factory->getFalse();
        });
        
        // Class methods
        
        // new
        $this->registerClassMethod('new', function(SOLClass $receiver, array $arguments) use ($factory): SOLObject {
            if (count($arguments) !== 0) {
                throw new DoNotUnderstandException('Class method new expected 0 arguments');
            }
            
            return $receiver->newInstance();
        });
        
        // from:
        $this->registerClassMethod('from:', function(SOLClass $receiver, array $arguments) use ($factory, $env): SOLObject {
            if (count($arguments) !== 1) {
                throw new DoNotUnderstandException('Class method from: expected 1 argument');
            }
            
            $obj = $arguments[0];
            $objClass = $obj->getClass();
            
            // Check if obj's class is compatible with receiver
            if (!($objClass->isSubclassOf($receiver) || $receiver->isSubclassOf($objClass))) {
                throw new DoNotUnderstandException("Incompatible class for from: method");
            }
            
            // Create a new instance and copy attributes
            $newInstance = $receiver->newInstance();
            
            // For special types, handle appropriately - this will be expanded later
            
            return $newInstance;
        });
    }
    
    /**
     * Register a built-in instance method in the Object class
     * 
     * @param string $selector Method selector
     * @param callable $implementation Method implementation
     */
    private function registerMethod(string $selector, callable $implementation): void
    {
        // Implementation will be added later using a proper method registry
    }
    
    /**
     * Register a built-in class method in the Object class
     * 
     * @param string $selector Method selector
     * @param callable $implementation Method implementation
     */
    private function registerClassMethod(string $selector, callable $implementation): void
    {
        // Implementation will be added later using a proper method registry
    }
}