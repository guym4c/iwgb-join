<?php

namespace IWGB\Join\Action\Api\Onboarding;

use GuzzleHttp\Exception\GuzzleException;
use IWGB\Join\Action\GenericAction;
use IWGB\Join\TypeHinter;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use GuzzleHttp\Psr7\Request as GuzzleRequest;

class JobTypeProxy extends GenericAction {

    private const JOB_TYPE_WORKSPACE = 'mhvN5b';
    private const TYPEFORM_BASE_URL = 'https://api.typeform.com/forms';

    protected $http;

    public function __construct(Container $c) {
        parent::__construct($c);

        /** @var $c TypeHinter */
        $this->http = $c->http;
    }

    /**
     * {@inheritdoc}
     * @throws GuzzleException
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $data = $this->processTypeformProxy($request->getMethod(), $args);

        if (!empty($data)) {
            return $response->withJson(json_decode((string)$data->getBody(), true));
        }
        return $response->withStatus(204);
    }

    /**
     * @param string $method
     * @param array  $args
     * @return ResponseInterface
     * @throws GuzzleException
     */
    private function processTypeformProxy(string $method, array $args): ResponseInterface {
        switch ($method) {

            case 'GET':
                if (empty($args['id'])) {
                    return $this->typeformRequest('GET', [
                        'query' => [
                            'workspace_id' => self::JOB_TYPE_WORKSPACE,
                        ],
                    ]);
                }

                return $this->typeformRequest('GET', [], "/{$args['id']}");
                break;
        }
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array  $options
     * @return ResponseInterface
     * @throws GuzzleException
     */
    private function typeformRequest(string $method, array $options = [], string $uri = ''): ResponseInterface {
        return $this->http->send(new GuzzleRequest($method, self::TYPEFORM_BASE_URL . $uri, [
            'Authorization' => "Bearer {$this->settings['typeform']['api']}"
        ]), $options);
    }
}