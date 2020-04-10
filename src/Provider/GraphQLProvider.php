<?php

namespace Iwgb\Join\Provider;

use GraphQL\Doctrine\Helper\EntitySchemaBuilder;
use Iwgb\Join\Domain\SorterResult;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class GraphQLProvider implements ServiceProviderInterface {

    /**
     * {@inheritdoc}
     */
    public function register(Container $c) {

        $c['graphql'] = fn(): EntitySchemaBuilder =>
            (new EntitySchemaBuilder($c['em']))->build([
                'sorterResults' => SorterResult::class,
            ]);
    }
}