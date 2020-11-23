<?php

namespace Iwgb\Join\Handler\Typeform;

use Doctrine\ORM;
use Iwgb\Join\Log\Event;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class RedirectToDataForm extends AbstractTypeformHandler {

    /**
     * {@inheritDoc}
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $this->log->addInfo(Event::REDIRECT_TO_DATA);
        $this->em->flush();

        return $this->redirectToTypeform($this->settings['typeform']['coreQuestionsId'], $request, $response);
    }

}