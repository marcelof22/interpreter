<?php

namespace IPP\Student\Runtime\SOL;

/**
 * SOL25 Nil object (singleton)
 */
class SOLNil extends SOLObject
{
    /**
     * Create a new SOL Nil
     * 
     * @param SOLClass $class The Nil class
     */
    public function __construct(SOLClass $class)
    {
        parent::__construct($class);
    }
    
    /**
     * Get a string representation of nil
     */
    public function __toString(): string
    {
        return 'nil';
    }
}