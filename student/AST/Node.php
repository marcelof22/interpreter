<?php

namespace IPP\Student\AST;

/**
 * Base abstract class for all AST nodes
 */
abstract class Node
{
    /**
     * Accept method for Visitor pattern
     * 
     * @param NodeVisitor $visitor Visitor object
     * @return mixed Result of the visit operation
     */
    abstract public function accept(NodeVisitor $visitor): mixed;
}