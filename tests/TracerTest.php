<?php

    namespace Thomann\TwigCodeCoverageTests;

    use PHPUnit\Framework\TestCase;
    use Thomann\TwigCodeCoverage\RenderEngineCoverageDriver;
    use Thomann\TwigCodeCoverage\TemplateCoverageResult;
    use Thomann\TwigCodeCoverage\Tracer;
    use Twig\TwigFunction;

    class TracerTest extends TestCase {

        private static ?TwigFunction $functionStart;
        private static ?TwigFunction $functionEnd;

        static function startTestTracer (string $templateName) {
            static::$functionStart->getCallable()([], ['templateName' => $templateName]);
        }

        static function endTestTracer (string $templateName) {
            static::$functionEnd->getCallable()([], ['templateName' => $templateName]);
        }

        function testExpectsCorrectTracerFunctionNames () {
            $tracer = new Tracer('i_am_a_unique_namespace', $this->createMock(RenderEngineCoverageDriver::class));

            $this->assertSame('__start_coverage_tracer__i_am_a_unique_namespace', $tracer->getStarterFunctionName());
            $this->assertSame('__end_coverage_tracer__i_am_a_unique_namespace', $tracer->getEndingFunctionName());
        }

        function testExpectsTracerToCorrectlyCollectCodeCoverageOfEvaldCodeOnly () {
            $tracer = new Tracer('test', $this->getTestEngineDriver());

            static::$functionStart = $tracer->getTwigFunctionTraceStart();
            static::$functionEnd = $tracer->getTwigFunctionTraceEnd();

            $evalCode = $this->getEvalableCodeFragment('simple-test.twig');
            ob_start();
            eval($evalCode);
            ob_end_clean();

            $coverageResults = $tracer->getCoverages();
            $this->assertCount(1, $coverageResults);
            $this->assertInstanceOf(TemplateCoverageResult::class, $coverageResults[0]);
            $this->assertSame('simple-test.twig', $coverageResults[0]->getTemplateName());
            $this->assertEqualsWithDelta(66.67, $coverageResults[0]->getCalledLinesPercentage(), 0.0001);
            $this->assertSame([
                3 => 1,
                5 => 1,
                6 => -1,
                7 => -1,
                9 => 1,
                10 => 1,
            ], $coverageResults[0]->getAllLinesByCallStatus());
        }

        private function getTestEngineDriver () : RenderEngineCoverageDriver {
            return new class implements RenderEngineCoverageDriver {
                function getExecutionKeyRegularExpression(): string {
                    return '/' . preg_quote(__FILE__) . '\([0-9]+\)/';
                }

                function getFunctionStackOffset(): int {
                    return 3;
                }
            };
        }

        private function getEvalableCodeFragment (string $templateFileName) : string {
            $startTestingTracerFunctionName = __CLASS__ . '::startTestTracer';
            $endTestingTracerFunctionName = __CLASS__ . '::endTestTracer';

            return <<<TESTTEMPLATE
                   {$startTestingTracerFunctionName}('{$templateFileName}');
                   (function(){
                   echo "Hey there!";
                   
                   if (isset(\$foo)) {
                       echo "This line will not be executed";
                       exit('ok');
                   } else {
                       echo "This one will be foo bar baz";
                       echo "This one will be executed";
                   }
                   })();
                   {$endTestingTracerFunctionName}('{$templateFileName}');
                TESTTEMPLATE;
        }

    }