<?php

    namespace Thomann\TwigCodeCoverageTests;

    use PHPUnit\Framework\TestCase;
    use Thomann\TwigCodeCoverage\Bootstrapper;
    use Thomann\TwigCodeCoverage\Tracer;
    use Twig\Environment;
    use Twig\Loader\FilesystemLoader;

    class TwigCoverageTest extends TestCase {

        private Environment $twig;
        private Tracer $tracer;

        function setUp(): void {
            $this->tracer = Bootstrapper::createTwigTracer();
            $this->twig = Bootstrapper::createCodeCoverageTwigEnvironment($this->tracer, new FilesystemLoader(__DIR__ . DIRECTORY_SEPARATOR . 'templates'));
        }

        function testExpectsTwigTemplateToBeRenderedWithCoverageTraced () {
            $html = $this->twig->render('hello.twig');

            $coverages = $this->tracer->getCoverages();
            $this->assertCount(1, $coverages);

            $coverage = array_shift($coverages);
            $this->assertSame('hello.twig', $coverage->getTemplateName());
            $this->assertSame([39, 40, 41, 47, 48, 49, 50, 58, 60, 63, 64, 65, 67], $coverage->getCalledLines());
            $this->assertSame([52, 53, 54, 56], $coverage->getUncalledLines());
            $this->assertSame(
                <<<ExpectedHtml
                Hello !
                
                You can do
                <ul>
                    <li>Nothing</li>
                </ul>
                here

                ExpectedHtml, $html);
        }

        function testExpectsIncludedTemplateToBeTraced () {
            $html = $this->twig->render('with-include.twig');

            $coverages = $this->tracer->getCoverages();
            $this->assertCount(2, $coverages);

            $expectedCoverages = [
                [
                    'hello.twig',
                    /* called lines   */ [39, 40, 41, 47, 48, 49, 50, 58, 60, 63, 64, 65, 67],
                    /* uncalled lines */ [52, 53, 54, 56],
                ],
                [
                    'with-include.twig',
                    /* called lines   */ [39, 42, 48, 52],
                    /* uncalled lines */ [44],
                ],
            ];

            foreach ($expectedCoverages as $index => [$expectedTemplateName, $expectedLinesCalled, $expectedLinesUncalled]) {
                $coverage = $coverages[$index];
                $this->assertSame($expectedTemplateName, $coverage->getTemplateName());
                $this->assertSame($expectedLinesCalled, $coverage->getCalledLines());
                $this->assertSame($expectedLinesUncalled, $coverage->getUncalledLines());
            }

            $this->assertSame(
                <<<ExpectedHtml
                <div class="with-include-wrapper">
                Hello !
                
                You can do
                <ul>
                    <li>Nothing</li>
                </ul>
                here
                </div>

                ExpectedHtml, $html);
        }

    }