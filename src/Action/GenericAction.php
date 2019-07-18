<?php

namespace IWGB\Join\Action;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use IWGB\Join\Domain\Applicant;
use IWGB\Join\TypeHinter;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

abstract class GenericAction {

    protected $log;

    protected $settings;

    protected $em;

    protected $airtable;

    protected $session;

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

    protected static function redirectToTypeform(string $formId, Applicant $applicant, Response $response): ResponseInterface {
        return $response->withRedirect(sprintf("%s/{$formId}?aid={$applicant->getId()}",
            self::TYPEFORM_FORM_BASE_URL));
    }

    protected function getApplicant(): Applicant {

        /** @var Applicant $applicant */
        $applicant = $this->em->getRepository(Applicant::class)
            ->find($this->session->get(self::SESSION_AID_KEY));

        if (empty($applicant))
            ;//error

        return $applicant;
    }
}