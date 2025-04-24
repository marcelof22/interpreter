<?php

namespace IPP\Student\Runtime\SOL;

use IPP\Student\AST\ClassNode;
use IPP\Student\AST\Method;
use IPP\Student\Exception\DoNotUnderstandException;
use IPP\Student\Runtime\Interpreter;

/**
 * Represents a SOL25 class at runtime
 */
class SOLClass extends SOLObject
{
    /** @var array<string, Method> */
    private array $methods = [];
    private ?SOLClass $parent = null;
    private ?ClassNode $classNode = null;
    
    /**
     * Create a new SOL class
     * 
     * @param string $name Class name
     * @param ClassNode|null $classNode AST class node (null for built-in classes)
     * @param SOLClass|null $selfClass Class to use as self's class
     */
    public function __construct(
        private string $name,
        ?ClassNode $classNode = null,
        ?SOLClass $selfClass = null
    ) {
        $this->classNode = $classNode;
        
        // For built-in classes, classNode will be null
        if ($classNode !== null) {
            // Load methods from the class node
            foreach ($classNode->getMethods() as $methodNode) {
                $this->methods[$methodNode->getSelector()] = $methodNode;
            }
        }
        
        // Parent will be set later in the second pass
        // We'll pass self or selfClass as the class for now
        parent::__construct($selfClass ?? $this);
    }
    
    /**
     * Get the class name
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * Set the parent class
     * 
     * @param SOLClass $parent Parent class
     */
    public function setParent(SOLClass $parent): void
    {
        $this->parent = $parent;
    }
    
    /**
     * Get the parent class
     */
    public function getParent(): ?SOLClass
    {
        return $this->parent;
    }
    
    /**
     * Get the AST class node
     * 
     * @return ClassNode|null AST class node or null for built-in classes
     */
    public function getClassNode(): ?ClassNode
    {
        return $this->classNode;
    }
    
    /**
     * Look up a method by selector
     * 
     * @param string $selector Method selector
     * @param bool $useSuper Whether to start lookup from parent
     * @return Method|null Method or null if not found
     */
    public function lookupMethod(string $selector, bool $useSuper = false): ?Method
    {
        // If super is used, start from parent
        if ($useSuper) {
            return $this->parent ? $this->parent->lookupMethod($selector) : null;
        }
        
        // Otherwise look in this class first
        if (isset($this->methods[$selector])) {
            return $this->methods[$selector];
        }
        
        // Then try parent
        return $this->parent ? $this->parent->lookupMethod($selector) : null;
    }
    
    /**
     * Create a new instance of this class
     * 
     * @return SOLObject New instance
     */
    public function newInstance(): SOLObject
    {
        return new SOLObject($this);
    }
    
    /**
     * Invoke a method on an object
     * 
     * @param SOLObject $receiver Receiver object
     * @param string $selector Method selector
     * @param SOLObject[] $arguments Method arguments
     * @param bool $useSuper Whether to use super for method lookup
     * @return SOLObject Result of the method invocation
     * @throws DoNotUnderstandException If the method is not found
     */
    public function invokeMethod(
        SOLObject $receiver,
        string $selector,
        array $arguments,
        bool $useSuper = false
    ): SOLObject {
        $method = $this->lookupMethod($selector, $useSuper);
        
        if (!$method) {
            throw new DoNotUnderstandException("Method '$selector' not found in class '{$this->name}'");
        }
        
        // Use the runtime interpreter instead of a direct static call
        $interpreter = \IPP\Student\Runtime\Interpreter::getInstance();
        return $interpreter->executeMethod($receiver, $method, $arguments, $useSuper);
    }
    
    /**
     * Get the class registry
     * 
     * @return \IPP\Student\Runtime\ClassRegistry The class registry
     */
    public function getClassRegistry(): \IPP\Student\Runtime\ClassRegistry
    {
        // Pokud tato třída je sama Object, je třeba najít její ClassRegistry
        if ($this->name === 'Object' && $this->parent === null) {
            // Získáme wrapper z atributů
            $wrapper = $this->getAttribute('classRegistry');
            
            // Pokud je wrapper specializovaný objekt s registry
            if ($wrapper instanceof SOLObject && method_exists($wrapper, 'getInternalRegistry')) {
                return $wrapper->getInternalRegistry();
            }
            
            // Pokud není ClassRegistry, vrátíme výchozí (nemělo by nastat)
            return new \IPP\Student\Runtime\ClassRegistry();
        }
        
        // Jinak získat ClassRegistry z nadtřídy
        if ($this->parent) {
            return $this->parent->getClassRegistry();
        }
        
        // Nemělo by nastat, ale pro jistotu vracíme nový registry
        return new \IPP\Student\Runtime\ClassRegistry();
    }

    /**
     * Set a class registry
     * 
     * @param \IPP\Student\Runtime\ClassRegistry $registry Class registry
     */
    public function setClassRegistry(\IPP\Student\Runtime\ClassRegistry $registry): void
    {
        // Jelikož SOLObject podporuje ukládání pouze SOLObject instancí jako atributy,
        // musíme registry uložit do vhodné struktury
        
        // Vytvoříme specializovaný wrapper objekt, který je SOLObject a může ukládat registry
        $wrapper = new class($this) extends SOLObject {
            private \IPP\Student\Runtime\ClassRegistry $registry;
            
            public function setInternalRegistry(\IPP\Student\Runtime\ClassRegistry $registry): void {
                $this->registry = $registry;
            }
            
            public function getInternalRegistry(): \IPP\Student\Runtime\ClassRegistry {
                return $this->registry;
            }
        };
        
        // Nastavíme registry do wrapperu
        $wrapper->setInternalRegistry($registry);
        
        // Uložíme wrapper do atributů
        parent::setAttribute('classRegistry', $wrapper);
    }
    
    /**
     * Check if this class is a subclass of another class
     * 
     * @param SOLClass $otherClass Other class
     * @return bool True if this class is a subclass of the other class
     */
    public function isSubclassOf(SOLClass $otherClass): bool
    {
        if ($this === $otherClass) {
            return true;
        }
        
        if (!$this->parent) {
            return false;
        }
        
        return $this->parent->isSubclassOf($otherClass);
    }
}