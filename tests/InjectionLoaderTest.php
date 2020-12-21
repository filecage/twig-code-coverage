<?php

    namespace Thomann\TwigCodeCoverageTests;

    use PHPUnit\Framework\MockObject\MockObject;
    use PHPUnit\Framework\TestCase;
    use Twig\Loader\LoaderInterface;
    use Twig\Source;
    use Thomann\TwigCodeCoverage\InjectionLoader;
    use Thomann\TwigCodeCoverage\Tracer;

    class InjectionLoaderTest extends TestCase {

        function testExpectsSourceWithInjectionFunctionCalls () {
            $tracer = new Tracer('unittest');
            $loader = new InjectionLoader($tracer, $this->getMockLoader('I am a template'));

            $source = $loader->getSourceContext('test.twig');

            $this->assertSame("{{ __start_coverage_tracer__unittest(templateName: 'test.twig') }}\nI am a template\n{{ __end_coverage_tracer__unittest(templateName: 'test.twig') }}", $source->getCode());
        }

        private function getMockLoader (string $templateContents, string $templateName = 'test.twig') : LoaderInterface {
            /** @var LoaderInterface|MockObject $loader */
            $loader = $this->createMock(LoaderInterface::class);
            $loader->method('getSourceContext')->willReturn(new Source($templateContents, $templateName));

            return $loader;
        }

    }