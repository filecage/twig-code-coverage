<?php

    namespace Thomann\TwigCodeCoverageTests;

    use PHPUnit\Framework\TestCase;
    use Thomann\TwigCodeCoverage\Tracer;

    class TracerTest extends TestCase {

        function testExpectsCorrectTracerFunctionNames () {
            $tracer = new Tracer('i_am_a_unique_namespace');

            $this->assertSame('__start_coverage_tracer__i_am_a_unique_namespace', $tracer->getStarterFunctionName());
            $this->assertSame('__end_coverage_tracer__i_am_a_unique_namespace', $tracer->getEndingFunctionName());
        }

    }