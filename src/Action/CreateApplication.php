<?php

namespace IWGB\Join\Action;

use Exception;
use IWGB\Join\Config;
use IWGB\Join\Domain\Applicant;
use IWGB\Join\JsonConfigObject;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
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

        return $response->withRedirect(sprintf("https://%s.typeform.com/to/{$job['typeform-id']}?aid=%s",
                self::TYPEFORM_USERNAME,
                $applicant->getId()));
    }
}