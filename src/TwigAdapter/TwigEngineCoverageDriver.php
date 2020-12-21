<?php

    namespace Thomann\TwigCodeCoverage\TwigAdapter;

    use Thomann\TwigCodeCoverage\RenderEngineCoverageDriver;

    class TwigEngineCoverageDriver implements RenderEngineCoverageDriver {
        function getExecutionKeyRegularExpression(): string {
            $twigEnvironmentFile = preg_quote(str_replace('/', DIRECTORY_SEPARATOR, '/vendor/twig/twig/src/Environment.php'));

            // todo: if the eval() moves to a different line in Environment.php (which is most likely to happen soon), this will break
            return "/.+{$twigEnvironmentFile}\(358\) : eval\(\)'d code/";
        }

        function getFunctionStackOffset(): int {
            return 2;
        }
    }