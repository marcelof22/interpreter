<?php

namespace IPP\Student\AST;

/**
 * Interface for visitor pattern implementation
 */
interface NodeVisitor
{
    public function visitProgram(Program $program): mixed;
    public function visitClass(ClassNode $class): mixed;
    public function visitMethod(Method $method): mixed;
    public function visitBlock(Block $block): mixed;
    public function visitParameter(Parameter $parameter): mixed;
    public function visitAssignment(Assignment $assignment): mixed;
    public function visitLiteral(Literal $literal): mixed;
    public function visitVariable(Variable $variable): mixed;
    public function visitMessageSend(MessageSend $messageSend): mixed;
}