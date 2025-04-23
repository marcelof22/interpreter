<?php

namespace IPP\Student\AST;

/**
 * AST node representing the entire program
 */
class Program extends Node
{
    /**
     * @param ClassNode[] $classes Classes defined in the program
     */
    public function __construct(
        private array $classes = []
    ) {
    }

    /**
     * @return ClassNode[]
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * Add a class to the program
     */
    public function addClass(ClassNode $class): void
    {
        $this->classes[] = $class;
    }

    /**
     * Find a class by name
     */
    public function findClass(string $className): ?ClassNode
    {
        foreach ($this->classes as $class) {
            if ($class->getName() === $className) {
                return $class;
            }
        }
        return null;
    }

    /**
     * Accept method for Visitor pattern
     */
    public function accept(NodeVisitor $visitor): mixed
    {
        return $visitor->visitProgram($this);
    }
}