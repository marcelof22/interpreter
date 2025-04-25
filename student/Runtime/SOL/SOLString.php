<?php

namespace IPP\Student\Runtime\SOL;

/**
 * SOL25 String object
 */
class SOLString extends SOLObject
{
    /**
     * Create a new SOL String
     * 
     * @param SOLClass $class The String class
     * @param string $value String value
     */
    public function __construct(
        SOLClass $class,
        private string $value
    ) {
        parent::__construct($class);
    }
    
    /**
     * Get the string value
     */
    public function getValue(): string
    {
        return $this->value;
    }
    
    /**
     * Get a string representation of this string
     */
    public function __toString(): string
    {
        return "'{$this->value}'";
    }

    public function printValue(): void
    {
        // Zpracuj escape sekvence
        $value = $this->getValue();
        $value = str_replace("\\n", "\n", $value);
        $value = str_replace("\\\\", "\\", $value);
        $value = str_replace("\\'", "'", $value);
        $value = str_replace('\\"', '"', $value);
        
        // Vypiš zpracovaný řetězec
        echo $value;
    }


}