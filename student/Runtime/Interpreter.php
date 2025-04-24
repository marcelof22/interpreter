<?php

namespace IPP\Student\Runtime;

use IPP\Student\AST\Assignment;
use IPP\Student\AST\Block;
use IPP\Student\AST\Expression;
use IPP\Student\AST\Literal;
use IPP\Student\AST\MessageSend;
use IPP\Student\AST\Method;
use IPP\Student\AST\NodeVisitor;
use IPP\Student\AST\Program;
use IPP\Student\AST\ClassNode;
use IPP\Student\AST\Parameter;
use IPP\Student\AST\Variable;
use IPP\Student\Exception\DoNotUnderstandException;
use IPP\Student\Runtime\SOL\SOLClass;
use IPP\Student\Runtime\SOL\SOLObject;
use IPP\Student\Exception\TypeErrorException;

/**
 * Interpreter for executing SOL25 programs
 */
class Interpreter implements NodeVisitor
{
    /** @var Interpreter|null */
    private static ?Interpreter $instance = null;
    
    private Environment $environment;
    private InputOutputHandler $io;
    
    /**
     * Get the Interpreter instance (singleton)
     */
    public static function getInstance(): Interpreter
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Create a new interpreter
     */
    private function __construct()
    {
        $this->environment = new Environment();
        $this->io = new InputOutputHandler();
    }
    
    /**
     * Set the environment
     */
    public function setEnvironment(Environment $environment): void
    {
        $this->environment = $environment;
    }
    
    /**
     * Get the runtime environment
     */
    public function getEnvironment(): Environment
    {
        return $this->environment;
    }
    
    /**
     * Get the input/output handler
     */
    public function getIO(): InputOutputHandler
    {
        return $this->io;
    }
    
    /**
     * Set the input/output handler
     */
    public function setIO(InputOutputHandler $io): void
    {
        $this->io = $io;
    }
    
    /**
     * Execute a SOL25 program
     * 
     * @param Program $program AST program
     * @return SOLObject Result of program execution
     * @throws DoNotUnderstandException If the program cannot be executed
     */
    public function execute(Program $program): SOLObject
    {
        // Initialize the environment
        $this->environment->initialize();
        
        // Create user-defined classes
        $this->environment->getClassRegistry()->createClassesFromAST($program->getClasses());
        
        // Find the Main class
        $mainClass = $program->findClass('Main');
        if (!$mainClass) {
            throw new DoNotUnderstandException('Class Main not found');
        }
        
        // Find the run method
        $runMethod = $mainClass->findMethod('run');
        if (!$runMethod) {
            throw new DoNotUnderstandException('Method run not found in class Main');
        }
        
        // Create instance of Main
        $mainClassObj = $this->environment->getClassRegistry()->lookupClass('Main');
        $mainInstance = $mainClassObj->newInstance();
        
        // Execute the run method
        return $this->executeMethod($mainInstance, $runMethod, [], false);
    }
    
    /**
     * Execute a method on an object
     * 
     * @param SOLObject $receiver The receiver object
     * @param Method $method The method to execute
     * @param SOLObject[] $arguments Method arguments
     * @param bool $useSuper Whether to use super for method execution
     * @return SOLObject Result of method execution
     * @throws DoNotUnderstandException If method execution fails
     */
    public function executeMethod(
        SOLObject $receiver,
        Method $method,
        array $arguments,
        bool $useSuper = false
    ): SOLObject {
        $block = $method->getBody();
        return $this->executeBlock($block, $receiver, $arguments, $useSuper);
    }
    
    /**
     * Execute a block with given arguments
     * 
     * @param Block $block The block to execute
     * @param SOLObject $self The self object for this execution
     * @param SOLObject[] $arguments Block arguments
     * @param bool $useSuper Whether to use super for method lookup
     * @return SOLObject Result of block execution
     * @throws DoNotUnderstandException If block execution fails
     */
    public function executeBlock(
        Block $block,
        SOLObject $self,
        array $arguments = [],
        bool $useSuper = false
    ): SOLObject {
        // Create a new frame for this block execution
        $frame = $this->environment->enterFrame($self, $useSuper);
        
        try {
            // Bind arguments to parameters
            $parameters = $block->getParameters();
            for ($i = 0; $i < count($parameters); $i++) {
                $parameter = $parameters[$i];
                
                // Check for argument existence
                if (!isset($arguments[$i])) {
                    throw new DoNotUnderstandException("Missing argument for parameter '{$parameter->getName()}'");
                }
                
                // Set parameter value
                $frame->setVariable($parameter->getName(), $arguments[$i]);
            }
            
            // Execute block statements
            $lastResult = $this->environment->getObjectFactory()->getNil();
            foreach ($block->getStatements() as $statement) {
                $result = $this->visitAssignment($statement);
                $lastResult = $result;
            }
            
            return $lastResult;
        } finally {
            // Always exit the frame
            $this->environment->exitFrame();
        }
    }
    
    /**
     * Visit a program node
     */
    public function visitProgram(Program $program): mixed
    {
        return $this->execute($program);
    }
    
    /**
     * Visit a class node
     */
    public function visitClass(ClassNode $class): mixed
    {
        // Classes are handled during initialization
        return null;
    }
    
    /**
     * Visit a method node
     */
    public function visitMethod(Method $method): mixed
    {
        // Methods are handled during execution
        return null;
    }
    
    /**
     * Visit a block node
     */
    public function visitBlock(Block $block): mixed
    {
        // Block execution is handled by executeBlock
        return null;
    }
    
    /**
     * Visit a parameter node
     */
    public function visitParameter(Parameter $parameter): mixed
    {
        // Parameters are handled during block execution
        return null;
    }
    
    /**
     * Visit an assignment node
     * 
     * @return SOLObject The result of the assignment
     */
    public function visitAssignment(Assignment $assignment): SOLObject
    {
        // Evaluate the expression
        $value = $assignment->getExpression()->accept($this);
        
        // Ensure value is a SOLObject
        if (!($value instanceof SOLObject)) {
            throw new TypeErrorException("Expression did not evaluate to a SOLObject");
        }
        
        // Check for special case: assignment to '_' (discard value)
        if ($assignment->getVariableName() === '_') {
            return $value;
        }
        
        // Store in the current frame
        $this->environment->defineVariable($assignment->getVariableName(), $value);
        
        return $value;
    }
    
    /**
     * Visit a literal node
     */
    public function visitLiteral(Literal $literal): mixed
    {
        // Create object from literal
        return $this->environment->getObjectFactory()->createFromLiteral(
            $literal->getClass(),
            $literal->getValue()
        );
    }
    
    /**
     * Visit a variable node
     */
    public function visitVariable(Variable $variable): mixed
    {
        $name = $variable->getName();
        
        // Special variables: true, false, nil
        switch ($name) {
            case 'true':
                return $this->environment->getObjectFactory()->getTrue();
            case 'false':
                return $this->environment->getObjectFactory()->getFalse();
            case 'nil':
                return $this->environment->getObjectFactory()->getNil();
        }
        
        // Regular variable lookup
        return $this->environment->lookupVariable($name);
    }
    
    /**
     * Visit a message send node
     * 
     * @param MessageSend $messageSend Message send node
     * @return SOLObject The result of sending the message
     * @throws TypeErrorException If the receiver or arguments do not evaluate to SOLObjects
     */
    public function visitMessageSend(MessageSend $messageSend): SOLObject
    {
        // Evaluate the receiver
        $receiverResult = $messageSend->getReceiver()->accept($this);
        
        // Ensure receiver is a SOLObject
        if (!($receiverResult instanceof SOLObject)) {
            throw new TypeErrorException("Receiver did not evaluate to a SOLObject");
        }
        
        $receiver = $receiverResult;
        $receiverExpr = $messageSend->getReceiver();
        
        // Check for special case: super
        $useSuper = false;
        if ($receiverExpr instanceof Variable && $receiverExpr->getName() === 'super') {
            // Odstraněna zbytečná kontrola instanceof SOLObject
            $useSuper = true;
        }
        
        // Evaluate all arguments
        $arguments = [];
        foreach ($messageSend->getArguments() as $argument) {
            $argResult = $argument->accept($this);
            if (!($argResult instanceof SOLObject)) {
                throw new TypeErrorException("Argument did not evaluate to a SOLObject");
            }
            $arguments[] = $argResult;
        }
        
        // Send the message
        return $receiver->sendMessage($messageSend->getSelector(), $arguments, $useSuper);
    }
}