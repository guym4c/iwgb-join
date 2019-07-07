<?php

namespace IWGB\Join\Action;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use IWGB\Join\TypeHinter;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

abstract class GenericAction {

    protected $http;

    protected $log;

    protected $settings;

    protected $em;

    const INVALID_INPUT_RETURN_URL = 'https://iwgb.org.uk/join';
    const TYPEFORM_USERNAME = 'iwgb';

    public function __construct(Container $c) {
        /** @var $c TypeHinter */

        $this->http = $c->http;
        $this->log = $c->log;
        $this->settings = $c->settings;
        $this->em = $c->em;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param string[] $args
     * @return ResponseInterface
     */
    abstract public function __invoke(Request $request, Response $response, array $args): ResponseInterface;

    /**
     * Fluent persist() wrapper
     *
     * @param object $entity
     * @return EntityManager
     * @throws ORMException
     */
    protected function persist($entity): EntityManager {
        $this->em->persist($entity);
        return $this->em;
    }
}