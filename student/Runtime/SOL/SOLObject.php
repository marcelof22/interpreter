<?php

namespace IPP\Student\Runtime\SOL;

use IPP\Student\Exception\DoNotUnderstandException;

/**
 * Base class for all SOL25 objects at runtime
 */
class SOLObject
{
    /** @var array<string, SOLObject> */
    private array $attributes = [];
    
    /**
     * Create a new SOL object
     * 
     * @param SOLClass $class The object's class
     */
    public function __construct(
        private SOLClass $class
    ) {
    }
    
    /**
     * Get the object's class
     */
    public function getClass(): SOLClass
    {
        return $this->class;
    }
    
    /**
     * Get an attribute value
     * 
     * @param string $name Attribute name
     * @return SOLObject|null Attribute value or null if not found
     */
    public function getAttribute(string $name): ?SOLObject
    {
        return $this->attributes[$name] ?? null;
    }
    
    /**
     * Set an attribute value
     * 
     * @param string $name Attribute name
     * @param SOLObject $value Attribute value
     * @return SOLObject $this for method chaining (returns self)
     */
    public function setAttribute(string $name, SOLObject $value): SOLObject
    {
        $this->attributes[$name] = $value;
        return $this;
    }
    
    /**
     * Check if the object has an attribute
     * 
     * @param string $name Attribute name
     * @return bool True if the attribute exists
     */
    public function hasAttribute(string $name): bool
    {
        return isset($this->attributes[$name]);
    }
    
    /**
     * Send a message to this object
     * 
     * @param string $selector Message selector
     * @param SOLObject[] $arguments Message arguments
     * @param bool $useSuper Whether to use super for method lookup
     * @return SOLObject Result of the message send
     * @throws DoNotUnderstandException If the message is not understood
     */
    public function sendMessage(string $selector, array $arguments, bool $useSuper = false): SOLObject
    {
        // First check if it's an attribute access (no arguments)
        if (empty($arguments) && !$useSuper && $this->hasAttribute($selector)) {
            $attribute = $this->getAttribute($selector);
            if ($attribute === null) {
                throw new DoNotUnderstandException("Attribute '$selector' not found");
            }
            return $attribute;
        }
        
        // Then check if it's an attribute setter (one argument)
        if (count($arguments) === 1 && !$useSuper && substr($selector, -1) === ':') {
            $attrName = substr($selector, 0, -1);
            return $this->setAttribute($attrName, $arguments[0]);
        }
        
        // Check if it's a built-in method
        $registry = $this->class->getClassRegistry()->getBuiltInMethodRegistry();
        $className = $this->class->getName();
        
        // Je-li to třída, zkontrolovat třídní metody
        $isClass = $this instanceof SOLClass;
        if ($isClass) {
            $method = $registry->lookupClassMethod($className, $selector);
            if ($method) {
                // Use Interpreter instance to get the environment
                $interpreter = \IPP\Student\Runtime\Interpreter::getInstance();
                $env = $interpreter->getEnvironment();
                return $method->execute($env, $this, $arguments);
            }
        } else {
            // Jinak zkontrolovat instanční metody
            $method = $registry->lookupMethod($className, $selector);
            if ($method) {
                // Use Interpreter instance to get the environment
                $interpreter = \IPP\Student\Runtime\Interpreter::getInstance();
                $env = $interpreter->getEnvironment();
                return $method->execute($env, $this, $arguments);
            }
        }
        
        // If it's for the class itself, delegate to the class
        return $this->class->invokeMethod($this, $selector, $arguments, $useSuper);
    }
    
    /**
     * Get a string representation of this object
     */
    public function __toString(): string
    {
        return "a " . $this->class->getName();
    }
}