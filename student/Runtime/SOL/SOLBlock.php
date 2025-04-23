<?php

namespace IPP\Student\Runtime\SOL;

use IPP\Student\AST\Block;
use IPP\Student\Exception\DoNotUnderstandException;
use IPP\Student\Runtime\Environment;
use IPP\Student\Runtime\Frame;
use IPP\Student\Runtime\Interpreter;

/**
 * SOL25 Block object
 */
class SOLBlock extends SOLObject
{
    /**
     * Create a new SOL Block
     * 
     * @param SOLClass $class The Block class
     * @param Block $block AST block node
     * @param Frame|null $lexicalFrame Lexical parent frame (for closures)
     */
    public function __construct(
        SOLClass $class,
        private Block $block,
        private ?Frame $lexicalFrame = null
    ) {
        parent::__construct($class);
    }
    
    /**
     * Get the AST block node
     */
    public function getBlock(): Block
    {
        return $this->block;
    }
    
    /**
     * Get the lexical parent frame
     */
    public function getLexicalFrame(): ?Frame
    {
        return $this->lexicalFrame;
    }
    
    /**
     * Execute the block with the given arguments
     * 
     * @param Environment $env The current environment
     * @param SOLObject $self The self object for this execution
     * @param SOLObject[] $arguments Block arguments
     * @return SOLObject Result of the block execution
     * @throws DoNotUnderstandException If the number of arguments doesn't match the block arity
     */
    public function execute(Environment $env, SOLObject $self, array $arguments): SOLObject
    {
        $arity = $this->block->getArity();
        if (count($arguments) !== $arity) {
            throw new DoNotUnderstandException(
                "Block arity mismatch: expected $arity arguments, got " . count($arguments)
            );
        }
        
        return Interpreter::getInstance()->executeBlock($this->block, $self, $arguments);
    }
    
    /**
     * Get a string representation of this block
     */
    public function __toString(): string
    {
        return 'a Block';
    }
}