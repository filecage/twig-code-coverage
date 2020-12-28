<?php

    namespace Thomann\TwigCodeCoverageTests;

    use PHPUnit\Framework\TestCase;
    use Thomann\TwigCodeCoverage\NestableCoverageContainer;

    class NestableCoverageTest extends TestCase {

        function testExpectsNestableCoverageToOnlyIterateLinesBetweenStartingAndEndingLine () {
            $nestableCoverage = new NestableCoverageContainer(4, null);
            $nestableCoverage->addCoverageResult([1 => false, 2 => false, 6 => true, 8 => true, 12 => false]);

            $executedAndFilteredLines = iterator_to_array($nestableCoverage->finalize(10));
            $this->assertSame([6 => true, 8 => true], $executedAndFilteredLines);
        }

        function testExpectsNestableCoverageToThrowExceptionWhenAlteredAfterFinalization () {
            $nestableCoverage = new NestableCoverageContainer(4, null);
            $nestableCoverage->addCoverageResult([]);

            // Iterator needs to run
            $nestableCoverage->finalize(10)->valid();

            $this->expectExceptionMessage('Can not add coverage result to NestableCoverage that is finished');
            $nestableCoverage->addCoverageResult([]);
        }

    }