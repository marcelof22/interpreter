<?php

namespace IPP\Student;

use IPP\Core\AbstractInterpreter;
use IPP\Core\ReturnCode;
use IPP\Student\AST\Program;
use IPP\Student\Exception\DoNotUnderstandException;
use IPP\Student\Exception\TypeErrorException;
use IPP\Student\Exception\ValueErrorException;
use IPP\Student\Runtime\Environment;
use IPP\Student\Runtime\InputOutputHandler;
use IPP\Student\Runtime\Interpreter as RuntimeInterpreter;
use IPP\Student\XML\ASTBuilder;

/**
 * Main interpreter class that implements the IPP\Core\AbstractInterpreter
 */
class Interpreter extends AbstractInterpreter
{
    private ASTBuilder $astBuilder;
    private RuntimeInterpreter $interpreter;
    private InputOutputHandler $ioHandler;
    
    /**
     * Initialize the interpreter
     */
    protected function init(): void
    {
        parent::init();
        
        $this->astBuilder = new ASTBuilder();
        $this->interpreter = RuntimeInterpreter::getInstance();
        $this->ioHandler = new InputOutputHandler();
        
        // Set up I/O
        $this->ioHandler->setInput($this->input);
        $this->ioHandler->setOutput($this->stdout);
        $this->ioHandler->setError($this->stderr);
        
        // Set the I/O handler in the interpreter
        $this->interpreter->setIO($this->ioHandler);
    }
    
    /**
     * Execute the program
     */
    public function execute(): int
    {
        try {
            // Parse the XML to AST
            $xml = $this->source->getDOMDocument();
            $program = $this->astBuilder->buildFromXML($xml);
            
            // Execute the program
            $this->interpreter->execute($program);
            
            // Success
            return ReturnCode::OK;
        } catch (DoNotUnderstandException $e) {
            // Runtime error - message not understood
            $this->stderr->writeString($e->getMessage() . PHP_EOL);
            return ReturnCode::INTERPRET_DNU_ERROR;
        } catch (TypeErrorException $e) {
            // Runtime error - type error
            $this->stderr->writeString($e->getMessage() . PHP_EOL);
            return ReturnCode::INTERPRET_TYPE_ERROR;
        } catch (ValueErrorException $e) {
            // Runtime error - value error
            $this->stderr->writeString($e->getMessage() . PHP_EOL);
            return ReturnCode::INTERPRET_VALUE_ERROR;
        }
    }
}