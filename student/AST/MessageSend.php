<?php

namespace IPP\Student\AST;

/**
 * AST node representing a message send expression
 */
class MessageSend extends Expression
{
    /**
     * @param string $selector Message selector
     * @param Expression $receiver Message receiver expression
     * @param Expression[] $arguments Message arguments
     */
    public function __construct(
        private string $selector,
        private Expression $receiver,
        private array $arguments = []
    ) {
    }

    /**
     * Get message selector
     */
    public function getSelector(): string
    {
        return $this->selector;
    }

    /**
     * Get message receiver
     */
    public function getReceiver(): Expression
    {
        return $this->receiver;
    }

    /**
     * @return Expression[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Add argument to message
     */
    public function addArgument(Expression $argument): void
    {
        $this->arguments[] = $argument;
    }

    /**
     * Accept method for Visitor pattern
     */
    public function accept(NodeVisitor $visitor): mixed
    {
        return $visitor->visitMessageSend($this);
    }
    
    /**
     * Evaluate the message send expression
     * 
     * @param \IPP\Student\Runtime\Environment $env Runtime environment
     * @param \IPP\Student\Runtime\SOL\SOLObject $self Self object for context
     * @return \IPP\Student\Runtime\SOL\SOLObject Result of expression evaluation
     */
    public function evaluate(\IPP\Student\Runtime\Environment $env, \IPP\Student\Runtime\SOL\SOLObject $self): \IPP\Student\Runtime\SOL\SOLObject
    {
        // Vyhodnotit příjemce
        $receiver = $this->receiver->evaluate($env, $self);
        
        // Zjistit, zda se jedná o super
        $useSuper = false;
        if ($this->receiver instanceof Variable && $this->receiver->getName() === 'super') {
            $useSuper = true;
        }
        
        // Vyhodnotit argumenty
        $arguments = [];
        foreach ($this->arguments as $argument) {
            $arguments[] = $argument->evaluate($env, $self);
        }
        
        // Poslat zprávu příjemci
        return $receiver->sendMessage($this->selector, $arguments, $useSuper);
    }
}