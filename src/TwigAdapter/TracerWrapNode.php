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
            $tracerArguments = new Node(['templateName' => new ConstantExpression($this->templateName, $this->lineno)]);
            // Start tracing
            $compiler->subcompile(new TwigFunctionInvocationNode($this->tracer->getTwigFunctionTraceStart(), $tracerArguments), false);

            // We need to wrap the code into a closure after starting the tracer
            // This is a known behaviour from xdebug, @see https://bugs.xdebug.org/view.php?id=1917
            $compiler->subcompile(new ClosureOpenNode());
            $compiler->raw(PHP_EOL);

            $compiler->indent();
            parent::compile($compiler);
            $compiler->outdent();
            $compiler->subcompile(new ClosureEndNode());
            $compiler->raw(PHP_EOL);

            // Stop tracing
            $compiler->subcompile(new TwigFunctionInvocationNode($this->tracer->getTwigFunctionTraceEnd(), $tracerArguments), false);
        }

    }