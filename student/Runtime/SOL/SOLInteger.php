<?php

namespace IPP\Student\Runtime\SOL;

/**
 * SOL25 Integer object
 */
class SOLInteger extends SOLObject
{
    /**
     * Create a new SOL Integer
     * 
     * @param SOLClass $class The Integer class
     * @param int $value Integer value
     */
    public function __construct(
        SOLClass $class,
        private int $value
    ) {
        parent::__construct($class);
    }
    
    /**
     * Get the integer value
     */
    public function getValue(): int
    {
        return $this->value;
    }
    
    /**
     * Get a string representation of this integer
     */
    public function __toString(): string
    {
        return (string)$this->value;
    }
}