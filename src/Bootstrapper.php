<?php

    namespace Thomann\TwigCodeCoverage;

    use Thomann\TwigCodeCoverage\TwigAdapter\TracerWrapParser;
    use Thomann\TwigCodeCoverage\TwigAdapter\TwigEngineCoverageDriver;
    use Thomann\TwigCodeCoverage\TwigAdapter\WrappedTagTokenParser;
    use Twig\Environment;
    use Twig\Loader\LoaderInterface;
    use Twig\TokenParser\IncludeTokenParser;

    class Bootstrapper {

        static function createTwigTracer () : Tracer {
            return new Tracer(uniqid(), new TwigEngineCoverageDriver());
        }

        static function createCodeCoverageTwigEnvironment (Tracer $tracer, LoaderInterface $templateLoader) : Environment {
            $twig = new Environment(new InjectionLoader($tracer, $templateLoader));

            $twig->addTokenParser(new TracerWrapParser($tracer));
            $twig->addFunction($tracer->getTwigFunctionTraceStart());
            $twig->addFunction($tracer->getTwigFunctionTraceEnd());
            $twig->addFunction($tracer->getTwigFunctionContinue());

            $twig->addTokenParser(new WrappedTagTokenParser('include', new IncludeTokenParser(), $tracer));

            return $twig;
        }

    }