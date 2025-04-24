<?php

namespace IPP\Student\Runtime;

use IPP\Student\AST\ClassNode;
use IPP\Student\Exception\DoNotUnderstandException;
use IPP\Student\Runtime\BuiltIn\BuiltInMethodRegistry;
use IPP\Student\Runtime\SOL\SOLClass;
use IPP\Student\Exception\InternalErrorException;

/**
 * Registry of all classes in the program
 */
class ClassRegistry
{
    /** @var array<string, SOLClass> */
    private array $classes = [];
    
    /** @var BuiltInMethodRegistry */
    private BuiltInMethodRegistry $builtInMethodRegistry;
    
    /**
     * Create a new class registry
     */
    public function __construct()
    {
        $this->builtInMethodRegistry = new BuiltInMethodRegistry();
    }
    
    /**
     * Get the built-in method registry
     */
    public function getBuiltInMethodRegistry(): BuiltInMethodRegistry
    {
        return $this->builtInMethodRegistry;
    }
    
    /**
     * Register a class in the registry
     * 
     * @param SOLClass $class The class to register
     */
    public function registerClass(SOLClass $class): void
    {
        $this->classes[$class->getName()] = $class;
    }
    
    /**
     * Look up a class by name
     * 
     * @param string $name Class name
     * @return SOLClass The found class
     * @throws DoNotUnderstandException If the class is not found
     */
    public function lookupClass(string $name): SOLClass
    {
        if (!isset($this->classes[$name])) {
            throw new DoNotUnderstandException("Class '$name' not found");
        }
        
        return $this->classes[$name];
    }
    
    /**
     * Check if a class exists
     * 
     * @param string $name Class name
     * @return bool True if the class exists
     */
    public function hasClass(string $name): bool
    {
        return isset($this->classes[$name]);
    }
    
    /**
     * Initialize built-in classes
     * 
     * @param ObjectFactory $factory Object factory for creating instances
     */
    public function initializeBuiltInClasses(ObjectFactory $factory): void
    {
        // Vytvoříme a inicializujeme vestavěné třídy pomocí BuiltInClassInitializer
        $initializer = new BuiltIn\BuiltInClassInitializer();
        $initializer->initializeBuiltInClasses($this, $factory);
        
        // Ujistíme se, že všechny potřebné třídy jsou registrovány
        $requiredClasses = ['Object', 'Nil', 'True', 'False', 'Integer', 'String', 'Block'];
        foreach ($requiredClasses as $className) {
            if (!$this->hasClass($className)) {
                throw new InternalErrorException("Built-in class '$className' was not properly initialized");
            }
        }
    }
    
    /**
     * Create SOL classes from AST class nodes
     * 
     * @param ClassNode[] $classNodes AST class nodes
     */
    public function createClassesFromAST(array $classNodes): void
    {
        // First pass: Create class objects without methods
        foreach ($classNodes as $classNode) {
            $name = $classNode->getName();
            $parentName = $classNode->getParent();
            
            // Create the class (methods will be added later)
            $class = new SOLClass($name, $classNode);
            $this->registerClass($class);
        }
        
        // Second pass: Set parent classes and add methods
        foreach ($classNodes as $classNode) {
            $name = $classNode->getName();
            $parentName = $classNode->getParent();
            
            $class = $this->lookupClass($name);
            
            // Set parent class
            if ($this->hasClass($parentName)) {
                $parentClass = $this->lookupClass($parentName);
                $class->setParent($parentClass);
            } else {
                throw new DoNotUnderstandException("Parent class '$parentName' not found for class '$name'");
            }
        }
    }
}