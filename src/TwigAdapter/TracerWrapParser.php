<?php

    namespace Thomann\TwigCodeCoverage\TwigAdapter;

    use Thomann\TwigCodeCoverage\Tracer;
    use Twig\Token;
    use Twig\TokenParser\AbstractTokenParser;

    class TracerWrapParser extends AbstractTokenParser {
        private const TWIG_TAG = '__templateCoverage';
        private Tracer $tracer;

        function __construct (Tracer $tracer) {
            $this->tracer = $tracer;
        }

        function parse(Token $token) {
            $stream = $this->parser->getStream();

            $stream->expect(/* Token::BLOCK_END_TYPE */ 3);
            $children = $this->parser->subparse(fn(Token $token) => $token->test('end__templateCoverage'));

            $stream->expect(Token::NAME_TYPE, 'end' . self::TWIG_TAG);
            $stream->expect(Token::BLOCK_END_TYPE);

            // todo: replace internal `getSourceComponent` with something from the public API
            return new TracerWrapNode($this->tracer, $children, $token->getLine(), $stream->getSourceContext()->getName());
        }

        function getTag() {
            return self::TWIG_TAG;
        }
    }