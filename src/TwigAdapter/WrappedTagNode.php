<?php

namespace Thomann\TwigCodeCoverage\TwigAdapter;

use Twig\Compiler;
use Twig\Node\Node;
use Twig\TwigFunction;

/**
 * @internal
 */
class WrappedTagNode extends Node {
    private Node $innerNode;
    private TwigFunction $continueCoverageFunction;

    function __construct(Node $innerNode, TwigFunction $continueCoverageFunction) {
        parent::__construct();
        $this->innerNode = $innerNode;
        $this->continueCoverageFunction = $continueCoverageFunction;
    }

    function compile(Compiler $compiler) {
        $this->innerNode->compile($compiler);

        $compiler->outdent();
        $compiler->subcompile(new ClosureEndNode());
        $compiler->raw(' /* re-activate tracer */ ');
        $compiler->subcompile(new TwigFunctionInvocationNode($this->continueCoverageFunction));
        $compiler->subcompile(new ClosureOpenNode());
        $compiler->raw(PHP_EOL);
        $compiler->indent();
    }

}