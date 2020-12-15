<?php

    namespace Thomann\TwigCodeCoverage;

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
            return sprintf("{{ %1\$s('%3\$s') }}\n%4\$s\n{{ %2\$s('%3\$s') }}",
                $this->tracer->getStarterFunctionName(),
                $this->tracer->getEndingFunctionName(),
                $templateFilename,
                $source
            );
        }

    }