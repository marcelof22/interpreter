<?php

namespace IPP\Student\AST;

/**
 * AST node representing an assignment statement
 */
class Assignment extends Node
{
    /**
     * @param string $variableName Variable name
     * @param Expression $expression Expression to assign
     */
    public function __construct(
        private string $variableName,
        private Expression $expression
    ) {
    }

    /**
     * Get variable name
     */
    public function getVariableName(): string
    {
        return $this->variableName;
    }

    /**
     * Get expression
     */
    public function getExpression(): Expression
    {
        return $this->expression;
    }

    /**
     * Accept method for Visitor pattern
     */
    public function accept(NodeVisitor $visitor): mixed
    {
        return $visitor->visitAssignment($this);
    }
}