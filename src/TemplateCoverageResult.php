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

        /**
         * @return int[]
         */
        function getCalledLines(): array {
            return array_keys(array_filter($this->codeCoverageLines, fn(int $calledFlag) => $calledFlag === self::EXECUTION_FLAG_CALLED));
        }

        /**
         * @return int[]
         */
        function getUncalledLines(): array {
            return array_keys(array_filter($this->codeCoverageLines, fn(int $calledFlag) => $calledFlag === self::EXECUTION_FLAG_UNCALLED));
        }

        /**
         * @return array
         */
        function getAllLinesByCallStatus() : array {
            return $this->codeCoverageLines;
        }

        /**
         * @return string value between 0 and 100, with a precision of 4
         */
        function getCalledLinesPercentage(): string {
            $calledLinesCount = count($this->getCalledLines());
            $uncalledLinesCount = count($this->getUncalledLines());
            if ($uncalledLinesCount === 0) {
                return '0';
            }

            return round($calledLinesCount / ($calledLinesCount + $uncalledLinesCount), 4) * 100;
        }

    }