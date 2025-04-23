<?php

namespace IPP\Student\AST;

/**
 * AST node representing a method definition
 */
class Method extends Node
{
    /**
     * @param string $selector Method selector
     * @param Block $body Method body
     */
    public function __construct(
        private string $selector,
        private Block $body
    ) {
    }

    /**
     * Get method selector
     */
    public function getSelector(): string
    {
        return $this->selector;
    }

    /**
     * Get method body
     */
    public function getBody(): Block
    {
        return $this->body;
    }

    /**
     * Accept method for Visitor pattern
     */
    public function accept(NodeVisitor $visitor): mixed
    {
        return $visitor->visitMethod($this);
    }
}