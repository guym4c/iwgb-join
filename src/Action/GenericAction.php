<?php

namespace IWGB\Join\Action;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use IWGB\Join\Domain\Applicant;
use IWGB\Join\TypeHinter;
use Psr\Http\Message\ResponseInterface;
use Sentry;
use Sentry\State\Scope;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

abstract class GenericAction {

    protected $log;

    protected $settings;

    protected $em;

    protected $airtable;

    protected $session;

    const ERROR_RETURN_URL = 'https://iwgb.org.uk/auth/login';
    const INVALID_INPUT_RETURN_URL = 'https://iwgb.org.uk/join';
    const TYPEFORM_FORM_BASE_URL = 'https://iwgb.typeform.com/to';
    const SESSION_AID_KEY = 'applicant';

    public function __construct(Container $c) {
        /** @var $c TypeHinter */

        $this->log = $c->log;
        $this->settings = $c->settings;
        $this->em = $c->em;
        $this->airtable = $c->airtable;
        $this->session = $c->session;
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

    protected static function redirectToTypeform(string $formId, Applicant $applicant, Response $response, array $query = []): ResponseInterface {
        $queryString = http_build_query(array_merge($query, [
            'aid' => $applicant->getId(),
        ]));
        return $response->withRedirect(sprintf("%s/{$formId}?{$queryString}",
            self::TYPEFORM_FORM_BASE_URL));
    }

    protected function getApplicant(): ?Applicant {

        /** @var Applicant $applicant */
        $applicant = $this->em->getRepository(Applicant::class)
            ->find($this->session->get(self::SESSION_AID_KEY));

        if (empty($applicant)) {
            $this->log->addNotice("Applicant {$this->session->get(self::SESSION_AID_KEY)} not found");
            return null;
        }

        Sentry\configureScope(function (Scope $scope) use ($applicant): void {
            $scope->setUser(['id' => $applicant->getId()]);
        });

        return $applicant;
    }

    protected function returnError(Response $response, string $error): ResponseInterface {
        $this->log->addError($error);
        return $response->withRedirect(self::ERROR_RETURN_URL . '?' . http_build_query([
                'e' => $error,
            ]));
    }
}