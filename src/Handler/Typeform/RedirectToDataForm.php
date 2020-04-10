<?php

namespace Iwgb\Join\Handler\Typeform;

use Doctrine\ORM;
use Iwgb\Join\Handler\RootHandler;
use Iwgb\Join\Log\Event;
use Iwgb\Join\Middleware\ApplicantSession;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class RedirectToDataForm extends RootHandler {

    /**
     * {@inheritDoc}
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $applicant = $this->getApplicant($request);

        if (empty($applicant)) {
            return ApplicantSession::sessionInvalid($response, $this->sm);
        }

        $this->log->addInfo(Event::REDIRECT_TO_DATA);
        $this->em->flush();

        return self::redirectToTypeform($this->settings['typeform']['coreQuestionsId'], $request, $response);
    }

}