<?php

namespace IWGB\Join\Action;

use Exception;
use IWGB\Join\Config;
use IWGB\Join\Domain\Applicant;
use IWGB\Join\JsonConfigObject;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class CreateApplication extends GenericAction {

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $job = JsonConfigObject::getItemByName(Config::JobTypes, $args['slug'], 'slug');

        //TODO jobs that bypass sorting

        if (empty($job))
            return $response->withRedirect(self::INVALID_INPUT_RETURN_URL);

        $applicant = new Applicant();
        $this->persist($applicant)->flush();
        $this->session->set(self::SESSION_AID_KEY, $applicant->getId());

        return self::redirectToTypeform($job['typeform-id'], $applicant, $response);
    }
}