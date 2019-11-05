<?php

namespace IWGB\Join\Provider;

use Doctrine\ORM\EntityManager;
use GraphQL\Doctrine\Helper\EntitySchemaBuilder;
use IWGB\Join\Domain\SorterResult;
use IWGB\Join\TypeHinter;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class GraphQLProvider implements ServiceProviderInterface {

    /** @var EntityManager */
    private $em;

    /** @var EntitySchemaBuilder */
    private $builder;

    /**
     * {@inheritdoc}
     */
    public function register(Container $c) {

        $c['graphql'] = function () use ($c): EntitySchemaBuilder {

            /** @var $c TypeHinter */
            $this->em = $c->em;

            $this->builder = new EntitySchemaBuilder($this->em);

            return $this->builder->build([
                'sorterResults' => SorterResult::class,
            ]);
        };
    }
}