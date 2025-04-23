<?php

namespace IPP\Student\AST;

/**
 * AST node representing a literal value
 */
class Literal extends Expression
{
    /**
     * @param string $class Literal class (Integer, String, True, False, Nil, class)
     * @param string $value Literal value
     */
    public function __construct(
        private string $class,
        private string $value
    ) {
    }

    /**
     * Get literal class
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Get literal value
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Accept method for Visitor pattern
     */
    public function accept(NodeVisitor $visitor): mixed
    {
        return $visitor->visitLiteral($this);
    }
    
    /**
     * Evaluate the literal expression
     * 
     * @param \IPP\Student\Runtime\Environment $env Runtime environment
     * @param \IPP\Student\Runtime\SOL\SOLObject $self Self object for context
     * @return \IPP\Student\Runtime\SOL\SOLObject Result of expression evaluation
     */
    public function evaluate(\IPP\Student\Runtime\Environment $env, \IPP\Student\Runtime\SOL\SOLObject $self): \IPP\Student\Runtime\SOL\SOLObject
    {
        // Use ObjectFactory to create corresponding SOL object
        return $env->getObjectFactory()->createFromLiteral($this->class, $this->value);
    }
}