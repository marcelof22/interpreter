<?php

namespace IPP\Student\Runtime;

use IPP\Student\Exception\DoNotUnderstandException;
use IPP\Student\Exception\TypeErrorException;
use IPP\Student\Exception\ValueErrorException;
use IPP\Student\Runtime\SOL\SOLObject;

/**
 * Runtime environment for executing SOL25 programs
 */
class Environment
{
    private ?Frame $currentFrame = null;
    private ClassRegistry $classRegistry;
    private ObjectFactory $objectFactory;
    
    /**
     * Create a new environment
     */
    public function __construct(?ClassRegistry $classRegistry = null, ?ObjectFactory $objectFactory = null)
    {
        $this->classRegistry = $classRegistry ?? new ClassRegistry();
        $this->objectFactory = $objectFactory ?? new ObjectFactory($this->classRegistry);
    }
    
    /**
     * Get the class registry
     */
    public function getClassRegistry(): ClassRegistry
    {
        return $this->classRegistry;
    }
    
    /**
     * Get the object factory
     */
    public function getObjectFactory(): ObjectFactory
    {
        return $this->objectFactory;
    }
    
    /**
     * Get the current frame
     */
    public function getCurrentFrame(): ?Frame
    {
        return $this->currentFrame;
    }
    
    /**
     * Define a variable in the current frame
     * 
     * @param string $name Variable name
     * @param SOLObject $value Variable value
     * @throws TypeErrorException If there is no current frame
     */
    public function defineVariable(string $name, SOLObject $value): void
    {
        if (!$this->currentFrame) {
            throw new TypeErrorException('Cannot define variable outside a frame');
        }
        
        $this->currentFrame->setVariable($name, $value);
    }
    
    /**
     * Look up a variable in the current frame
     * 
     * @param string $name Variable name
     * @return SOLObject Variable value
     * @throws DoNotUnderstandException If the variable is not defined
     */
    public function lookupVariable(string $name): SOLObject
    {
        if (!$this->currentFrame) {
            throw new TypeErrorException('Cannot lookup variable outside a frame');
        }
        
        $value = $this->currentFrame->getVariable($name);
        if ($value === null) {
            throw new DoNotUnderstandException("Variable '$name' is not defined");
        }
        
        return $value;
    }
    
    /**
     * Enter a new frame
     * 
     * @param SOLObject $self The self object for the frame
     * @param bool $isSuper Whether this is a super call
     * @return Frame The new frame
     */
    public function enterFrame(SOLObject $self, bool $isSuper = false): Frame
    {
        $frame = new Frame($self, $this->currentFrame, $isSuper);
        $this->currentFrame = $frame;
        return $frame;
    }
    
    /**
     * Exit the current frame
     * 
     * @return Frame|null The previous frame
     */
    public function exitFrame(): ?Frame
    {
        if (!$this->currentFrame) {
            return null;
        }
        
        $previousFrame = $this->currentFrame->getParent();
        $this->currentFrame = $previousFrame;
        return $previousFrame;
    }
    
    /**
     * Initialize the environment with built-in classes
     */
    public function initialize(): void
    {
        // Initialize built-in classes
        $this->classRegistry->initializeBuiltInClasses($this->objectFactory);
        
        // Create singletons (nil, true, false) once classes are initialized
        $this->objectFactory->getNil();
        $this->objectFactory->getTrue();
        $this->objectFactory->getFalse();
    }
}