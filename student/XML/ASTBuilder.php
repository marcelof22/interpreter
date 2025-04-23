<?php

namespace IPP\Student\XML;

use DOMDocument;
use DOMElement;
use IPP\Core\Exception\XMLException;
use IPP\Student\AST\Assignment;
use IPP\Student\AST\Block;
use IPP\Student\AST\ClassNode;
use IPP\Student\AST\Expression;
use IPP\Student\AST\Literal;
use IPP\Student\AST\MessageSend;
use IPP\Student\AST\Method;
use IPP\Student\AST\Parameter;
use IPP\Student\AST\Program;
use IPP\Student\AST\Variable;
use IPP\Student\Exception\InvalidSourceStructureException;

/**
 * Builder for creating AST from XML
 */
class ASTBuilder
{
    /**
     * Build AST from XML document
     * 
     * @param DOMDocument $xml XML document
     * @return Program Program AST node
     * @throws XMLException If the XML structure is invalid
     */
    public function buildFromXML(DOMDocument $xml): Program
    {
        $programElement = $xml->documentElement;
        
        if (!$programElement || $programElement->nodeName !== 'program') {
            throw new XMLException('Invalid XML: root element must be <program>');
        }
        
        $program = new Program();
        
        // Process class elements
        $classElements = $programElement->getElementsByTagName('class');
        foreach ($classElements as $classElement) {
            $class = $this->processClass($classElement);
            $program->addClass($class);
        }
        
        return $program;
    }
    
    /**
     * Process a class element
     * 
     * @param DOMElement $element XML class element
     * @return ClassNode Class AST node
     * @throws InvalidSourceStructureException If the class element is invalid
     */
    private function processClass(DOMElement $element): ClassNode
    {
        $name = $element->getAttribute('name');
        $parent = $element->getAttribute('parent');
        
        if (!$name || !$parent) {
            throw new InvalidSourceStructureException('Invalid class: missing name or parent attribute');
        }
        
        $class = new ClassNode($name, $parent);
        
        // Process method elements
        $methodElements = $element->getElementsByTagName('method');
        foreach ($methodElements as $methodElement) {
            $method = $this->processMethod($methodElement);
            $class->addMethod($method);
        }
        
        return $class;
    }
    
    /**
     * Process a method element
     * 
     * @param DOMElement $element XML method element
     * @return Method Method AST node
     * @throws InvalidSourceStructureException If the method element is invalid
     */
    private function processMethod(DOMElement $element): Method
    {
        $selector = $element->getAttribute('selector');
        
        if (!$selector) {
            throw new InvalidSourceStructureException('Invalid method: missing selector attribute');
        }
        
        // Process block element (method body)
        $blockElements = $element->getElementsByTagName('block');
        if ($blockElements->length !== 1) {
            throw new InvalidSourceStructureException('Invalid method: must contain exactly one block');
        }
        
        $block = $this->processBlock($blockElements->item(0));
        
        return new Method($selector, $block);
    }
    
    /**
     * Process a block element
     * 
     * @param DOMElement $element XML block element
     * @return Block Block AST node
     * @throws InvalidSourceStructureException If the block element is invalid
     */
    private function processBlock(DOMElement $element): Block
    {
        $arity = (int)$element->getAttribute('arity');
        $block = new Block($arity);
        
        // Process parameter elements
        $paramElements = $element->getElementsByTagName('parameter');
        foreach ($paramElements as $paramElement) {
            $param = $this->processParameter($paramElement);
            $block->addParameter($param);
        }
        
        // Process assignment elements
        $assignElements = $element->getElementsByTagName('assign');
        foreach ($assignElements as $assignElement) {
            $assign = $this->processAssignment($assignElement);
            $block->addStatement($assign);
        }
        
        return $block;
    }
    
    /**
     * Process a parameter element
     * 
     * @param DOMElement $element XML parameter element
     * @return Parameter Parameter AST node
     * @throws InvalidSourceStructureException If the parameter element is invalid
     */
    private function processParameter(DOMElement $element): Parameter
    {
        $name = $element->getAttribute('name');
        $order = (int)$element->getAttribute('order');
        
        if (!$name || $order < 1) {
            throw new InvalidSourceStructureException('Invalid parameter: missing name or invalid order');
        }
        
        return new Parameter($name, $order);
    }
    
    /**
     * Process an assignment element
     * 
     * @param DOMElement $element XML assignment element
     * @return Assignment Assignment AST node
     * @throws InvalidSourceStructureException If the assignment element is invalid
     */
    private function processAssignment(DOMElement $element): Assignment
    {
        // Get variable element
        $varElements = $element->getElementsByTagName('var');
        if ($varElements->length !== 1) {
            throw new InvalidSourceStructureException('Invalid assignment: must contain exactly one var');
        }
        
        $varElement = $varElements->item(0);
        $varName = $varElement->getAttribute('name');
        
        if (!$varName) {
            throw new InvalidSourceStructureException('Invalid assignment: variable has no name');
        }
        
        // Get expression element
        $exprElements = $element->getElementsByTagName('expr');
        if ($exprElements->length !== 1) {
            throw new InvalidSourceStructureException('Invalid assignment: must contain exactly one expr');
        }
        
        $exprElement = $exprElements->item(0);
        $expression = $this->processExpression($exprElement);
        
        return new Assignment($varName, $expression);
    }
    
    /**
     * Process an expression element
     * 
     * @param DOMElement $element XML expression element
     * @return Expression Expression AST node
     * @throws InvalidSourceStructureException If the expression element is invalid
     */
    private function processExpression(DOMElement $element): Expression
    {
        // Check the expression type based on the child element
        $childNodes = $element->childNodes;
        foreach ($childNodes as $childNode) {
            if ($childNode->nodeType === XML_ELEMENT_NODE) {
                $nodeName = $childNode->nodeName;
                
                switch ($nodeName) {
                    case 'literal':
                        return $this->processLiteral($childNode);
                    case 'var':
                        return $this->processVariable($childNode);
                    case 'send':
                        return $this->processMessageSend($childNode);
                    case 'block':
                        return $this->processBlockExpression($childNode);
                    default:
                        throw new InvalidSourceStructureException("Invalid expression: unknown type '$nodeName'");
                }
            }
        }
        
        throw new InvalidSourceStructureException('Invalid expression: missing expression type');
    }
    
    /**
     * Process a literal element
     * 
     * @param DOMElement $element XML literal element
     * @return Literal Literal AST node
     * @throws InvalidSourceStructureException If the literal element is invalid
     */
    private function processLiteral(DOMElement $element): Literal
    {
        $class = $element->getAttribute('class');
        $value = $element->getAttribute('value');
        
        if (!$class) {
            throw new InvalidSourceStructureException('Invalid literal: missing class attribute');
        }
        
        return new Literal($class, $value);
    }
    
    /**
     * Process a variable element
     * 
     * @param DOMElement $element XML variable element
     * @return Variable Variable AST node
     * @throws InvalidSourceStructureException If the variable element is invalid
     */
    private function processVariable(DOMElement $element): Variable
    {
        $name = $element->getAttribute('name');
        
        if (!$name) {
            throw new InvalidSourceStructureException('Invalid variable: missing name attribute');
        }
        
        return new Variable($name);
    }
    
    /**
     * Process a message send element
     * 
     * @param DOMElement $element XML message send element
     * @return MessageSend MessageSend AST node
     * @throws InvalidSourceStructureException If the message send element is invalid
     */
    private function processMessageSend(DOMElement $element): MessageSend
    {
        $selector = $element->getAttribute('selector');
        
        if (!$selector) {
            throw new InvalidSourceStructureException('Invalid message send: missing selector attribute');
        }
        
        // Get receiver expression
        $exprElements = $element->getElementsByTagName('expr');
        if ($exprElements->length < 1) {
            throw new InvalidSourceStructureException('Invalid message send: missing receiver expression');
        }
        
        $receiverExpr = $this->processExpression($exprElements->item(0));
        $messageSend = new MessageSend($selector, $receiverExpr);
        
        // Process arguments
        $argElements = $element->getElementsByTagName('arg');
        foreach ($argElements as $argElement) {
            // Get argument expression
            $argExprElements = $argElement->getElementsByTagName('expr');
            if ($argExprElements->length !== 1) {
                throw new InvalidSourceStructureException('Invalid message send: argument must contain exactly one expr');
            }
            
            $argExpr = $this->processExpression($argExprElements->item(0));
            $messageSend->addArgument($argExpr);
        }
        
        return $messageSend;
    }
    
    /**
     * Process a block expression element
     * 
     * @param DOMElement $element XML block element
     * @return Block Block AST node
     */
    private function processBlockExpression(DOMElement $element): Block
    {
        return $this->processBlock($element);
    }
}