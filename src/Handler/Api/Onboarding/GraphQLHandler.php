<?php


namespace Iwgb\Join\Handler\Api\Onboarding;

use GraphQL\Doctrine\Helper\EntitySchemaBuilder;
use Iwgb\Join\Handler\RootHandler;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class GraphQLHandler extends RootHandler {

    protected EntitySchemaBuilder $graphql;

    public function __construct(Container $c) {
        parent::__construct($c);

        $this->graphql = $c['graphql'];
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        return $response->withJson(
            $this->graphql->getServer()
                ->executePsrRequest($request)
                ->toArray(true));
    }
}