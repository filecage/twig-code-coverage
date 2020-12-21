<?php

    namespace Thomann\TwigCodeCoverage\TwigAdapter;

    use Thomann\TwigCodeCoverage\Tracer;
    use Twig\Compiler;
    use Twig\Node\Expression\ConstantExpression;
    use Twig\Node\Expression\FunctionExpression;
    use Twig\Node\Node;

    class TracerWrapNode extends Node {

        private Tracer $tracer;
        private string $templateName;

        function __construct(Tracer $tracer, Node $body, int $lineNumber, string $templateName) {
            parent::__construct($body->nodes, $body->attributes, $lineNumber);
            $this->tracer = $tracer;
            $this->templateName = $templateName;
        }

        function compile(Compiler $compiler) {
            $templateNameArgumentsNode = new Node(['templateName' => new ConstantExpression($this->templateName, $this->lineno)]);

            // Start tracing
            $compiler->subcompile(new FunctionExpression($this->tracer->getTwigFunctionTraceStart()->getName(), $templateNameArgumentsNode, 0), false);
            $compiler->raw(';');

            // We need to wrap the code into a closure after starting the tracer
            // This is a known behaviour from xdebug, @see https://bugs.xdebug.org/view.php?id=1917
            $compiler->write("(function() use (\$context, \$blocks, \$macros) { // TRACER START\n");
            $compiler->indent();
            parent::compile($compiler);
            $compiler->outdent();
            $compiler->write("})(); // TRACER END\n");

            // Stop tracing
            $compiler->subcompile(new FunctionExpression($this->tracer->getTwigFunctionTraceEnd()->getName(), $templateNameArgumentsNode, 0), false);
            $compiler->raw(';');
        }

    }