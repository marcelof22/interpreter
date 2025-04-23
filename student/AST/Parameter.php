<?php

namespace IPP\Student\AST;

/**
 * AST node representing a block parameter
 */
class Parameter extends Node
{
    /**
     * @param string $name Parameter name
     * @param int $order Parameter order (1-based)
     */
    public function __construct(
        private string $name,
        private int $order
    ) {
    }

    /**
     * Get parameter name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get parameter order
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * Accept method for Visitor pattern
     */
    public function accept(NodeVisitor $visitor): mixed
    {
        return $visitor->visitParameter($this);
    }
}