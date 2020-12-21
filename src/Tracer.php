<?php

    namespace Thomann\TwigCodeCoverage;

    use Twig\TwigFunction;

    /**
     * @internal
     */
    class Tracer {

        private string $namespace;
        private RenderEngineCoverageDriver $renderEngineCoverageDriver;
        private ?NestableCoverage $currentCoverageRun = null;

        /** @var TemplateCoverageResult[] Array containing the coverages by template filename */
        private array $coverages = [];

        function __construct(string $namespace, RenderEngineCoverageDriver $renderEngineCoverageDriver) {
            $this->namespace = $namespace;
            $this->renderEngineCoverageDriver = $renderEngineCoverageDriver;
        }

        function getCoverages(): array {
            return $this->coverages;
        }

        /**
         * @internal
         */
        function getTwigFunctionTraceStart(): TwigFunction {
            return new TwigFunction($this->getFunctionName('start'), function (string $templateName) {
                // TODO: If there was a parent before we need to add the coverage that has been collected so far here
                $this->currentCoverageRun = new NestableCoverage($this->getCurrentCalleeLineNumber(), $this->currentCoverageRun);
                xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
            });
        }

        /**
         * @internal
         */
        function getTwigFunctionTraceEnd(): TwigFunction {
            return new TwigFunction($this->getFunctionName('end'), function (string $templateName) {
                $overallCoverageStatistics = xdebug_get_code_coverage();
                xdebug_stop_code_coverage(true);

                $this->currentCoverageRun->addCoverageResult($this->getRelevantCoverageStatistics($overallCoverageStatistics));
                $templateCoverageResult = iterator_to_array($this->currentCoverageRun->finalize($this->getCurrentCalleeLineNumber()));

                // Remove tracer function call parts from the coverage (the closure and the start/stop expressions)
                $templateCoverageResult = array_slice($templateCoverageResult, 1, -2, true);
                $this->coverages[] = new TemplateCoverageResult($templateName, $templateCoverageResult);

                $this->currentCoverageRun = $this->currentCoverageRun->getParent();
            });
        }

        private function getFunctionName(string $functionName): string {
            return sprintf('__%s_coverage_tracer__%s', $functionName, $this->namespace);
        }

        private function getRelevantCoverageStatistics(array $coverageStatistics): array {
            $renderEngineExecutionKeyRegex = $this->renderEngineCoverageDriver->getExecutionKeyRegularExpression();
            foreach ($coverageStatistics as $executionKey => $coverageStatistic) {
                if (preg_match($renderEngineExecutionKeyRegex, $executionKey) === 1) {
                    return $coverageStatistic;
                }
            }

            throw new \RuntimeException("Tracer could not find coverage statistics using execution key regular expression `{$renderEngineExecutionKeyRegex}`");
        }

        private function getCurrentCalleeLineNumber(): int {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $this->renderEngineCoverageDriver->getFunctionStackOffset());
            $actualCallee = array_pop($backtrace);

            return $actualCallee['line'];
        }

    }