<?php

    namespace Thomann\TwigCodeCoverage\TwigAdapter;

    use Thomann\TwigCodeCoverage\InjectionLoader;
    use Thomann\TwigCodeCoverage\Tracer;
    use Twig\Token;
    use Twig\TokenParser\AbstractTokenParser;

    class TracerWrapParser extends AbstractTokenParser {
        /**
         * @internal
         * @see InjectionLoader will be added automatically
         */
        const TWIG_WRAP_TAG_START = '__templateCoverage';

        /**
         * @internal
         * @see InjectionLoader will be added automatically
         */
        const TWIG_WRAP_TAG_END = '__templateCoverage_end';

        private Tracer $tracer;

        function __construct (Tracer $tracer) {
            $this->tracer = $tracer;
        }

        function parse(Token $token) {
            $stream = $this->parser->getStream();
            $templateName = $stream->expect(Token::STRING_TYPE);

            $stream->expect(/* Token::BLOCK_END_TYPE */ 3);
            $children = $this->parser->subparse(fn(Token $token) => $token->test(self::TWIG_WRAP_TAG_END));

            $stream->expect(Token::NAME_TYPE, self::TWIG_WRAP_TAG_END);
            $stream->expect(Token::BLOCK_END_TYPE);

            return new TracerWrapNode($this->tracer, $children, $token->getLine(), $templateName->getValue());
        }

        function getTag() {
            return self::TWIG_WRAP_TAG_START;
        }
    }