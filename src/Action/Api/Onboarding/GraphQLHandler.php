<?php


namespace IWGB\Join\Action\Api\Onboarding;

use IWGB\Join\Action\GenericAction;
use IWGB\Join\TypeHinter;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class GraphQLHandler extends GenericAction {

    protected $graphql;

    public function __construct(Container $c) {
        parent::__construct($c);

        /** @var $c TypeHinter */
        $this->graphql = $c->graphql;
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