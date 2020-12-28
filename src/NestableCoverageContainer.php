<?php

    namespace Thomann\TwigCodeCoverage;

    use Iterator;

    /**
     * @internal
     */
    class NestableCoverageContainer {

        private bool $finalized = false;
        private int $startingLineNumber;
        private array $coverageLines = [];
        private array $ignoreLines = [];
        private ?self $parent;

        function __construct(int $startingLineNumber, ?self $parent) {
            $this->startingLineNumber = $startingLineNumber;
            $this->parent = $parent;
        }

        function ignore (int $fromLineNumber, int $toLineNumber) : void {
            foreach (range($fromLineNumber, $toLineNumber) as $ignoredLineNumber) {
                $this->ignoreLines[] = $ignoredLineNumber;
                unset($this->coverageLines[$ignoredLineNumber]);
            }
        }

        function addCoverageResult(array $coverageLines): void {
            if ($this->finalized === true) {
                throw new \RuntimeException('Can not add coverage result to NestableCoverage that is finished');
            }

            foreach ($coverageLines as $lineNumber => $executionFlag) {
                if (!in_array($lineNumber, $this->ignoreLines)) {
                    $this->coverageLines[$lineNumber] = $executionFlag;
                }
            }
        }

        function finalize(int $endingLineNumber): Iterator {
            $this->finalized = true;

            foreach ($this->coverageLines as $lineNumber => $lineExecutionFlag) {
                if ($lineNumber < $this->startingLineNumber) {
                    continue;
                } elseif ($lineNumber > $endingLineNumber) {
                    break;
                }

                yield $lineNumber => $lineExecutionFlag;
            }
        }

        function getParent(): ?self {
            return $this->parent;
        }

    }