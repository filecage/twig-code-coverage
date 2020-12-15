<?php

    namespace Thomann\TwigCodeCoverage;

    use Twig\TwigFunction;

    /**
     * @internal
     */
    class Tracer {

        private string $namespace;

        /** @var array Array containing the coverages by template filename */
        private array $coverages = [];

        function __construct (string $namespace) {
            $this->namespace = $namespace;
        }

        function getCoverages () : array {
            return $this->coverages;
        }

        function getStarterFunctionName () : string {
            return $this->getFunctionName('start');
        }

        function getEndingFunctionName () : string {
            return $this->getFunctionName('end');
        }

        private function getFunctionName (string $functionName) : string {
            return sprintf('__%s_coverage_tracer__%s', $functionName, $this->namespace);
        }

    }