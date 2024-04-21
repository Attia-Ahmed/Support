<?php

namespace Illuminate\Database\Query {

    use Illuminate\Database\ConnectionInterface;
    use Attia\Support\Database\QueryBuilderMixin;
    use Illuminate\Database\Query\Grammars\Grammar;
    use Illuminate\Database\Query\Processors\Processor;

    if (false) {

        /**
         * @mixin QueryBuilderMixin
         */
        class Builder
        {
            public function __construct(ConnectionInterface $connection,
                Grammar $grammar = null,
                Processor $processor = null)
            {}
        }
    }
}

namespace Illuminate\Testing {

    use Attia\Support\Testing\TestResponseViewMixin;

    if (false) {

        /**
         * @mixin TestResponseViewMixin
         */
        class TestResponse
        {

        }
    }

}

