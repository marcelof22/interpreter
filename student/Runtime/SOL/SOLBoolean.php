<?php

namespace IPP\Student\Runtime\SOL;

/**
 * SOL25 Boolean object (True or False)
 */
class SOLBoolean extends SOLObject
{
    /**
     * Create a new SOL Boolean
     * 
     * @param SOLClass $class The Boolean class (True or False)
     * @param bool $value Boolean value
     */
    public function __construct(
        SOLClass $class,
        private bool $value
    ) {
        parent::__construct($class);
    }
    
    /**
     * Get the boolean value
     */
    public function getValue(): bool
    {
        return $this->value;
    }
    
    /**
     * Get a string representation of this boolean
     */
    public function __toString(): string
    {
        return $this->value ? 'true' : 'false';
    }
}