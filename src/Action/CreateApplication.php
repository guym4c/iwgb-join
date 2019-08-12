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

        if (empty($job)) {
            return $response->withRedirect(self::INVALID_INPUT_RETURN_URL);
        }

        $applicant = new Applicant();
        $this->persist($applicant)->flush();
        $this->session->set(self::SESSION_AID_KEY, $applicant->getId());

        if (!empty($job['bypass-sorter']) &&
            $job['bypass-sorter']) {
            $applicant->setBranch($job['branch-id']);
            $applicant->setPlan($job['membership-id']);
            $this->em->flush();
            return self::redirectToTypeform($this->settings['typeform']['core-questions-id'], $applicant, $response);
        }

        return self::redirectToTypeform($job['typeform-id'], $applicant, $response);
    }
}