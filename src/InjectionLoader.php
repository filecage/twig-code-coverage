<?php

    namespace Thomann\TwigCodeCoverage;

    use Thomann\TwigCodeCoverage\TwigAdapter\TracerWrapParser;
    use Twig\Loader\LoaderInterface;
    use Twig\Source;

    /**
     * @internal
     */
    class InjectionLoader implements LoaderInterface {
        private Tracer $tracer;
        /**
         * @var LoaderInterface
         */
        private LoaderInterface $innerLoader;

        function __construct(Tracer $tracer, LoaderInterface $innerLoader) {
            $this->tracer = $tracer;
            $this->innerLoader = $innerLoader;
        }

        function getSourceContext(string $name): Source {
            $this->tracer->notifyNextTemplateWillBeLoaded($name);
            $source = $this->innerLoader->getSourceContext($name);

            return new Source($this->wrapSourceCodeInTracerFunctions($source->getCode(), $source->getName()), $source->getName(), $source->getPath());
        }

        function getCacheKey(string $name): string {
            return $this->innerLoader->getCacheKey($name);
        }

        function isFresh(string $name, int $time): bool {
            return $this->innerLoader->isFresh($name, $time);
        }

        function exists(string $name) {
            return $this->innerLoader->exists($name);
        }

        private function wrapSourceCodeInTracerFunctions (string $source, string $templateFilename) : string {
            $tracerStartTag = TracerWrapParser::TWIG_WRAP_TAG_START;
            $tracerEndBlock = TracerWrapParser::TWIG_WRAP_TAG_END;
            return <<<TEMPLATE
                   {% {$tracerStartTag} '{$templateFilename}' %}
                   {$source}
                   {% {$tracerEndBlock} %}
                   TEMPLATE;
        }

    }