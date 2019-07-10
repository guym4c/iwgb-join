<?php

namespace IWGB\Join\Action\Typeform;

use IWGB\Join\Action\GenericAction;
use IWGB\Join\Config;
use IWGB\Join\Domain\Applicant;
use IWGB\Join\JsonConfigObject;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class RecallBranch extends GenericAction {

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        /** @var Applicant $applicant */
        $applicant = $this->em->getRepository(Applicant::class)
            ->find($args['aid']);

        if (empty($applicant))
            ;//error

        return GenericTypeformAction::redirectToTypeform(
            JsonConfigObject::getItemByName(Config::BranchForms, $applicant->getBranch(), 'branch-id')['form-id'],
            $applicant,
            $response);
    }
}