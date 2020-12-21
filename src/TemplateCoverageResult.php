<?php

    namespace Thomann\TwigCodeCoverage;

    use Twig\Source;

    class TemplateCoverageResult {
        private const EXECUTION_FLAG_CALLED = 1;
        private const EXECUTION_FLAG_UNCALLED = -1;
        private const EXECUTION_FLAG_UNCALLABLE = -2;

        private string $templateName;
        private array $codeCoverageLines;

        /**
         * @param string $templateName
         * @param array $codeCoverageLines
         * @internal
         */
        function __construct(string $templateName, array $codeCoverageLines) {
            $this->templateName = $templateName;
            $this->codeCoverageLines = $codeCoverageLines;
        }

        function getTemplateName(): string {
            return $this->templateName;
        }

        function getCalledLines(): array {
            return array_keys(array_filter($this->codeCoverageLines, fn(int $calledFlag) => $calledFlag === self::EXECUTION_FLAG_CALLED));
        }

        function getUncalledLines(): array {
            return array_keys(array_filter($this->codeCoverageLines, fn(int $calledFlag) => $calledFlag === self::EXECUTION_FLAG_UNCALLED));
        }

        function getAllLinesByCallStatus() : array {
            return $this->codeCoverageLines;
        }

        function getCalledLinesPercentage(): string {
            $calledLinesCount = count($this->getCalledLines());

            return round($calledLinesCount / ($calledLinesCount + count($this->getUncalledLines())), 4) * 100;
        }

    }