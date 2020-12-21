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
            $this->assertSame([38, 39, 40, 46, 47, 48, 49, 57, 59, 62, 63, 64, 66], $coverage->getCalledLines());
            $this->assertSame([51, 52, 53, 55], $coverage->getUncalledLines());
            $this->assertSame("Hello !

You can do
<ul>
    <li>Nothing</li>
</ul>
here
", $html);
        }

    }