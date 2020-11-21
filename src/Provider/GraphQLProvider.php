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

        $c[Provider::GRAPHQL] = fn(): EntitySchemaBuilder =>
            (new EntitySchemaBuilder($c[Provider::ENTITY_MANAGER], [
                'sorterResults' => SorterResult::class,
            ]));
    }
}