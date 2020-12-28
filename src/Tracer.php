<?php

    namespace Thomann\TwigCodeCoverage;

    use Twig\TwigFunction;

    final class Tracer {

        private string $namespace;
        private RenderEngineCoverageDriver $renderEngineCoverageDriver;
        private ?NestableCoverageContainer $currentCoverageRun = null;

        /** @var TemplateCoverageResult[] Array containing the coverages by template filename */
        private array $coverages = [];

        /**
         * @param string $namespace
         * @param RenderEngineCoverageDriver $renderEngineCoverageDriver
         * @internal
         */
        function __construct(string $namespace, RenderEngineCoverageDriver $renderEngineCoverageDriver) {
            $this->namespace = $namespace;
            $this->renderEngineCoverageDriver = $renderEngineCoverageDriver;
        }

        /**
         * Returns a list of all coverage results
         *
         * @return TemplateCoverageResult[]
         */
        function getCoverages(): array {
            return $this->coverages;
        }

        /**
         * Used to notify the Tracer that a new template will be loaded soon (causing the xdebug coverage to be
         * overwritten). If there is a coverage trace in progress, it means a template has been included and the
         * coverage buffer needs to be written to the current coverage container.
         *
         * @param string $templateName
         * @internal
         */
        function notifyNextTemplateWillBeLoaded (string $templateName) : void {
            if ($this->currentCoverageRun !== null) {
                $this->clearCoverageBufferAndWriteToContainer($this->currentCoverageRun);
            }
        }

        /**
         * @internal
         */
        function getTwigFunctionTraceStart(): TwigFunction {
            return new TwigFunction($this->getFunctionName('start'), function (string $templateName) {
                $this->currentCoverageRun = new NestableCoverageContainer($this->getCurrentCalleeLineNumber(), $this->currentCoverageRun);
                $this->currentCoverageRun->ignore($this->getCurrentCalleeLineNumber(), null);
                xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
            });
        }

        /**
         * @internal
         */
        function getTwigFunctionTraceEnd(): TwigFunction {
            return new TwigFunction($this->getFunctionName('end'), function (string $templateName) {
                $this->clearCoverageBufferAndWriteToContainer($this->currentCoverageRun);
                $templateCoverageResult = iterator_to_array($this->currentCoverageRun->finalize($this->getCurrentCalleeLineNumber()));

                // Remove tracer function call parts from the coverage (the closure and the start/stop expressions)
                $templateCoverageResult = array_slice($templateCoverageResult, 1, -2, true);
                $this->coverages[] = new TemplateCoverageResult($templateName, $templateCoverageResult);

                $this->currentCoverageRun = $this->currentCoverageRun->getParent();
            });
        }

        /**
         * @internal
         */
        function getTwigFunctionContinue() : TwigFunction {
            return new TwigFunction($this->getFunctionName('continue'), function () {
                // When we're continuing, we have to ignore this and the next line (next line is the next closure opener)
                $calleeLine = $this->getCurrentCalleeLineNumber();
                $this->currentCoverageRun->ignore($calleeLine, $calleeLine + 1);

                xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
            });
        }

        private function clearCoverageBufferAndWriteToContainer (NestableCoverageContainer $coverageContainer) : void {
            $overallCoverageStatistics = xdebug_get_code_coverage();
            xdebug_stop_code_coverage(true);

            $coverageContainer->addCoverageResult($this->getRelevantCoverageStatistics($overallCoverageStatistics));
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