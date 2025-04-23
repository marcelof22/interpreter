<?php

namespace IPP\Student\Runtime;

use IPP\Student\Exception\DoNotUnderstandException;
use IPP\Student\Exception\TypeErrorException;
use IPP\Student\Runtime\SOL\SOLBlock;
use IPP\Student\Runtime\SOL\SOLBoolean;
use IPP\Student\Runtime\SOL\SOLClass;
use IPP\Student\Runtime\SOL\SOLInteger;
use IPP\Student\Runtime\SOL\SOLNil;
use IPP\Student\Runtime\SOL\SOLObject;
use IPP\Student\Runtime\SOL\SOLString;

/**
 * Factory for creating SOL objects
 */
class ObjectFactory
{
    private ?SOLNil $nilInstance = null;
    private ?SOLBoolean $trueInstance = null;
    private ?SOLBoolean $falseInstance = null;
    
    /**
     * Create a new object factory
     * 
     * @param ClassRegistry $classRegistry Class registry for looking up classes
     */
    public function __construct(
        private ClassRegistry $classRegistry
    ) {
    }
    
    /**
     * Create a new object of the given class
     * 
     * @param string $className Class name
     * @return SOLObject The created object
     * @throws DoNotUnderstandException If the class is not found
     */
    public function createObject(string $className): SOLObject
    {
        $class = $this->classRegistry->lookupClass($className);
        return $class->newInstance();
    }
    
    /**
     * Create an object from a literal value
     * 
     * @param string $class Literal class (Integer, String, True, False, Nil, class)
     * @param string $value Literal value
     * @return SOLObject The created object
     * @throws TypeErrorException If the literal type is invalid
     */
    public function createFromLiteral(string $class, string $value): SOLObject
    {
        switch ($class) {
            case 'Integer':
                return $this->createInteger((int)$value);
            case 'String':
                return $this->createString($value);
            case 'True':
                return $this->getTrue();
            case 'False':
                return $this->getFalse();
            case 'Nil':
                return $this->getNil();
            case 'class':
                // Class literal (e.g., for Integer.new)
                if (!$this->classRegistry->hasClass($value)) {
                    throw new DoNotUnderstandException("Class '$value' not found");
                }
                return $this->classRegistry->lookupClass($value);
            default:
                throw new TypeErrorException("Invalid literal class: '$class'");
        }
    }
    
    /**
     * Create an integer object
     * 
     * @param int $value Integer value
     * @return SOLInteger The created integer object
     */
    public function createInteger(int $value): SOLInteger
    {
        $class = $this->classRegistry->lookupClass('Integer');
        return new SOLInteger($class, $value);
    }
    
    /**
     * Create a string object
     * 
     * @param string $value String value
     * @return SOLString The created string object
     */
    public function createString(string $value): SOLString
    {
        $class = $this->classRegistry->lookupClass('String');
        return new SOLString($class, $value);
    }
    
    /**
     * Create a block object
     * 
     * @param \IPP\Student\AST\Block $blockNode Block AST node
     * @return SOLBlock The created block object
     */
    public function createBlock(\IPP\Student\AST\Block $blockNode): SOLBlock
    {
        $class = $this->classRegistry->lookupClass('Block');
        return new SOLBlock($class, $blockNode);
    }
    
    /**
     * Get the nil instance (singleton)
     * 
     * @return SOLNil The nil instance
     */
    public function getNil(): SOLNil
    {
        if ($this->nilInstance === null) {
            $class = $this->classRegistry->lookupClass('Nil');
            $this->nilInstance = new SOLNil($class);
        }
        return $this->nilInstance;
    }
    
    /**
     * Get the true instance (singleton)
     * 
     * @return SOLBoolean The true instance
     */
    public function getTrue(): SOLBoolean
    {
        if ($this->trueInstance === null) {
            $class = $this->classRegistry->lookupClass('True');
            $this->trueInstance = new SOLBoolean($class, true);
        }
        return $this->trueInstance;
    }
    
    /**
     * Get the false instance (singleton)
     * 
     * @return SOLBoolean The false instance
     */
    public function getFalse(): SOLBoolean
    {
        if ($this->falseInstance === null) {
            $class = $this->classRegistry->lookupClass('False');
            $this->falseInstance = new SOLBoolean($class, false);
        }
        return $this->falseInstance;
    }
}