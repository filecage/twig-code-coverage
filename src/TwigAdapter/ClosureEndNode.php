<?php

    namespace Thomann\TwigCodeCoverage\TwigAdapter;

    use Twig\Compiler;
    use Twig\Node\Node;

    class ClosureEndNode extends Node {
        function compile(Compiler $compiler) {
            $compiler->write('})();');
        }
    }