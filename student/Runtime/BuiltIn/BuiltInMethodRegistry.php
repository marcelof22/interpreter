<?php

namespace IPP\Student\Runtime\BuiltIn;

use IPP\Student\Exception\DoNotUnderstandException;
use IPP\Student\Runtime\Environment;
use IPP\Student\Runtime\SOL\SOLClass;
use IPP\Student\Runtime\SOL\SOLObject;

/**
 * Registry for built-in methods
 */
class BuiltInMethodRegistry
{
    /** @var array<string, array<string, BuiltInMethod>> */
    private array $methods = [];
    
    /** @var array<string, array<string, BuiltInMethod>> */
    private array $classMethods = [];
    
    /**
     * Register a built-in instance method
     * 
     * @param string $className Class name
     * @param string $selector Method selector
     * @param BuiltInMethod $method Method implementation
     */
    public function registerMethod(string $className, string $selector, BuiltInMethod $method): void
    {
        if (!isset($this->methods[$className])) {
            $this->methods[$className] = [];
        }
        
        $this->methods[$className][$selector] = $method;
    }
    
    /**
     * Register a built-in class method
     * 
     * @param string $className Class name
     * @param string $selector Method selector
     * @param BuiltInMethod $method Method implementation
     */
    public function registerClassMethod(string $className, string $selector, BuiltInMethod $method): void
    {
        if (!isset($this->classMethods[$className])) {
            $this->classMethods[$className] = [];
        }
        
        $this->classMethods[$className][$selector] = $method;
    }
    
    /**
     * Look up a built-in instance method
     * 
     * @param string $className Class name
     * @param string $selector Method selector
     * @return BuiltInMethod|null Method implementation or null if not found
     */
    public function lookupMethod(string $className, string $selector): ?BuiltInMethod
    {
        return $this->methods[$className][$selector] ?? null;
    }
    
    /**
     * Look up a built-in class method
     * 
     * @param string $className Class name
     * @param string $selector Method selector
     * @return BuiltInMethod|null Method implementation or null if not found
     */
    public function lookupClassMethod(string $className, string $selector): ?BuiltInMethod
    {
        return $this->classMethods[$className][$selector] ?? null;
    }
    
    /**
     * Execute a built-in method
     * 
     * @param Environment $env Runtime environment
     * @param SOLObject $receiver Method receiver
     * @param string $selector Method selector
     * @param SOLObject[] $arguments Method arguments
     * @param bool $isClassMethod Whether this is a class method
     * @return SOLObject Method result
     * @throws DoNotUnderstandException If the method is not found
     */
    public function executeMethod(
        Environment $env,
        SOLObject $receiver,
        string $selector,
        array $arguments,
        bool $isClassMethod = false
    ): SOLObject {
        $className = $receiver instanceof SOLClass ? $receiver->getName() : $receiver->getClass()->getName();
        
        $method = $isClassMethod
            ? $this->lookupClassMethod($className, $selector)
            : $this->lookupMethod($className, $selector);
        
        if (!$method) {
            throw new DoNotUnderstandException("Built-in method '$selector' not found in class '$className'");
        }
        
        return $method->execute($env, $receiver, $arguments);
    }
}