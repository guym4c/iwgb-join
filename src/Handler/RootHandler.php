<?php

namespace Iwgb\Join\Handler;

use Aura\Session\Segment;
use Aura\Session\Session as SessionManager;
use Doctrine\ORM;
use Doctrine\ORM\EntityManager;
use Guym4c\Airtable\Airtable;
use Iwgb\Join\Domain\Applicant;
use Iwgb\Join\Handler\Api\Error\Error;
use Iwgb\Join\Handler\Api\Error\ErrorHandler;
use Iwgb\Join\Middleware\ApplicantSession;
use Iwgb\Join\Provider\Provider;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Sentry;
use Slim\Collection;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Router;

abstract class RootHandler {

    private const TYPEFORM_FORM_BASE_URL = 'https://iwgb.typeform.com/to';

    protected Logger $log;

    protected Collection $settings;

    protected EntityManager $em;

    protected Airtable $airtable;

    protected SessionManager $sm;

    protected Router $router;

    public function __construct(Container $c) {
        $this->log = $c[Provider::LOG];
        $this->settings = $c[Provider::SETTINGS];
        $this->em = $c[Provider::ENTITY_MANAGER];
        $this->airtable = $c[Provider::AIRTABLE];
        $this->sm = $c[Provider::SESSION];
        $this->router = $c[Provider::ROUTER];

        $this->router->setBasePath($this->settings['basePath']);
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
     * @throws ORM\ORMException
     */
    protected function persist($entity): EntityManager {
        $this->em->persist($entity);
        return $this->em;
    }

    protected function redirectToTypeform(
        string $formId,
        Request $request,
        Response $response,
        array $query = []
    ): ResponseInterface {
        $queryString = http_build_query(array_merge($query, [
            'aid' => $this->getApplicant($request)->getId(),
        ]));
        return $response->withRedirect(sprintf("%s/{$formId}?{$queryString}",
            self::TYPEFORM_FORM_BASE_URL));
    }

    protected function getApplicant(Request $request): ?Applicant {
        return $request->getAttribute('applicant');
    }

    protected function getSession(): Segment {
        return $this->sm->getSegment(ApplicantSession::class);
    }

    protected function redirectToRoute(Response $response, string $route): ResponseInterface {
        return $response->withRedirect(
            $this->router->relativePathFor($route)
        );
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param Error    $error
     * @param array    $context
     * @return ResponseInterface
     */
    protected function errorRedirect(Request $request, Response $response, Error $error, array $context = []): ResponseInterface {

        $this->log->addError(strtolower($error->getKey()), $context);

        try {
            $this->em->flush();
        } catch (ORM\ORMException $e) {
            Sentry\captureException($e);
        }

        return ErrorHandler::redirect(
            $response,
            $this->sm,
            $error,
            $this->getApplicant($request)
        );
    }
}