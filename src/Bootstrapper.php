<?php

    namespace Thomann\TwigCodeCoverage;

    use Thomann\TwigCodeCoverage\TwigAdapter\TracerWrapParser;
    use Thomann\TwigCodeCoverage\TwigAdapter\TwigEngineCoverageDriver;
    use Thomann\TwigCodeCoverage\TwigAdapter\WrappedTagTokenParser;
    use Twig\Environment;
    use Twig\Loader\LoaderInterface;
    use Twig\TokenParser\IncludeTokenParser;

    class Bootstrapper {

        /**
         * Creates a Tracer instance with a random namespace
         *
         * @return Tracer
         */
        static function createTwigTracer () : Tracer {
            return new Tracer(uniqid(), new TwigEngineCoverageDriver());
        }

        /**
         * Creates a wrapper for your template loader that injects the blocks necessary for coverage tracing
         *
         * @param Tracer $tracer
         * @param LoaderInterface $templateLoader
         * @return LoaderInterface
         */
        static function createLoader (Tracer $tracer, LoaderInterface $templateLoader) : LoaderInterface {
            return new InjectionLoader($tracer, $templateLoader);
        }

        /**
         * Enriches your Twig environment with all necessary functions and parsers
         *
         * @param Environment $twig
         * @param Tracer $tracer
         * @return Environment
         */
        static function enrichEnvironment (Tracer $tracer, Environment $twig) : Environment {
            $twig->addTokenParser(new WrappedTagTokenParser('include', new IncludeTokenParser(), $tracer));
            $twig->addTokenParser(new TracerWrapParser($tracer));
            $twig->addFunction($tracer->getTwigFunctionTraceStart());
            $twig->addFunction($tracer->getTwigFunctionTraceEnd());
            $twig->addFunction($tracer->getTwigFunctionContinue());

            return $twig;
        }

        /**
         * Creates a default traceable Twig environment
         *
         * @param Tracer $tracer
         * @param LoaderInterface $templateLoader
         * @return Environment
         */
        static function createCodeCoverageTwigEnvironment (Tracer $tracer, LoaderInterface $templateLoader) : Environment {
            $twig = new Environment(static::createLoader($tracer, $templateLoader));
            $twig = static::enrichEnvironment($tracer, $twig);

            return $twig;
        }

    }