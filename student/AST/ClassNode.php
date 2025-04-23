<?php

namespace IPP\Student\AST;

/**
 * AST node representing a class definition
 */
class ClassNode extends Node
{
    /**
     * @param string $name Class name
     * @param string $parent Parent class name
     * @param Method[] $methods Methods defined in the class
     */
    public function __construct(
        private string $name,
        private string $parent,
        private array $methods = []
    ) {
    }

    /**
     * Get class name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get parent class name
     */
    public function getParent(): string
    {
        return $this->parent;
    }

    /**
     * @return Method[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Add a method to the class
     */
    public function addMethod(Method $method): void
    {
        $this->methods[] = $method;
    }

    /**
     * Find a method by selector
     */
    public function findMethod(string $selector): ?Method
    {
        foreach ($this->methods as $method) {
            if ($method->getSelector() === $selector) {
                return $method;
            }
        }
        return null;
    }

    /**
     * Accept method for Visitor pattern
     */
    public function accept(NodeVisitor $visitor): mixed
    {
        return $visitor->visitClass($this);
    }
}