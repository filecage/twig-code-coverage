<?php

    namespace Thomann\TwigCodeCoverage;

    /**
     * @internal 
     */
    interface RenderEngineCoverageDriver {

        /**
         * Returns a regular expression to find the actual code coverage information in
         * the result of `xdebug_get_code_coverage()`
         *
         * @return string
         */
        function getExecutionKeyRegularExpression(): string;

        /**
         * Returns the function count between the tracer call and the actual render call
         * to determine the exact line numbers using a call stack
         *
         * When rewinding the call stack, this offset has to point at the index at which
         * the actual template render call has been made
         *
         * @see Tracer::getCurrentCalleeLineNumber
         * @return int
         */
        function getFunctionStackOffset(): int;

    }