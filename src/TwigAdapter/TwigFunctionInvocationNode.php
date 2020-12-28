<?php

    namespace Thomann\TwigCodeCoverage\TwigAdapter;

    use Twig\Compiler;
    use Twig\Node\Expression\FunctionExpression;
    use Twig\Node\Node;
    use Twig\TwigFunction;

    /**
     * @internal
     */
    class TwigFunctionInvocationNode extends Node {
        private TwigFunction $function;
        private ?Node $arguments;

        function __construct(TwigFunction $function, Node $arguments = null) {
            parent::__construct();
            $this->function = $function;
            $this->arguments = $arguments;
        }

        function compile(Compiler $compiler) {
            $compiler->subcompile(new FunctionExpression($this->function->getName(), $this->arguments ?? new Node(), 0));
            $compiler->raw(';' . PHP_EOL);
        }
    }