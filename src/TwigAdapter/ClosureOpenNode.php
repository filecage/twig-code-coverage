<?php

    namespace Thomann\TwigCodeCoverage\TwigAdapter;

    use Twig\Compiler;
    use Twig\Node\Node;

    class ClosureOpenNode extends Node {
        function compile(Compiler $compiler) {
            $compiler->write('(function() use ($context, $blocks, $macros){');
        }
    }