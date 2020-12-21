<?php

    namespace Thomann\TwigCodeCoverage;

    use Iterator;

    /**
     * @internal
     */
    class NestableCoverage {

        private bool $finalized = false;
        private string $fileName;
        private int $startingLineNumber;
        private array $coverageLines = [];
        private ?self $parent;

        function __construct(int $startingLineNumber, ?self $parent) {
            $this->startingLineNumber = $startingLineNumber;
            $this->parent = $parent;
        }

        function addCoverageResult(array $coverageLines): void {
            if ($this->finalized === true) {
                throw new \RuntimeException('Can not add coverage result to NestableCoverage that is finished');
            }

            foreach ($coverageLines as $lineNumber => $executionFlag) {
                $this->coverageLines[$lineNumber] = $executionFlag;
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