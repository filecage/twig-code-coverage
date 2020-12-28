<?php

    namespace Thomann\TwigCodeCoverage\TwigAdapter;

    use Thomann\TwigCodeCoverage\Tracer;
    use Twig\Error\SyntaxError;
    use Twig\Node\Node;
    use Twig\Parser;
    use Twig\Token;
    use Twig\TokenParser\AbstractTokenParser;

    class WrappedTagTokenParser extends AbstractTokenParser {

        private string $tagName;
        private AbstractTokenParser $innerTokenParser;
        private Tracer $tracer;

        function __construct (string $tagName, AbstractTokenParser $innerTokenParser, Tracer $tracer) {
            $this->tagName = $tagName;
            $this->innerTokenParser = $innerTokenParser;
            $this->tracer = $tracer;
        }

        /**
         * @param Parser $parser
         * @internal
         */
        function setParser(Parser $parser): void {
            parent::setParser($parser);

            $this->innerTokenParser->setParser($parser);
        }

        /**
         * @param Token $token
         * @return Node
         * @throws SyntaxError
         * @internal
         */
        function parse(Token $token) : Node {
            return new WrappedTagNode($this->innerTokenParser->parse($token), $this->tracer->getTwigFunctionContinue());
        }

        /**
         * @internal
         * @return string
         */
        function getTag() : string {
            return $this->tagName;
        }

    }