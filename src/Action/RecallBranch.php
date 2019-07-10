<?php

namespace IWGB\Join\Action;

use IWGB\Join\Config;
use IWGB\Join\JsonConfigObject;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class RecallBranch extends GenericAction {

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $applicant = $this->getApplicant($args);

        return self::redirectToTypeform(
            JsonConfigObject::getItemByName(Config::BranchForms, $applicant->getBranch(), 'branch-id')['form-id'],
            $applicant,
            $response);
    }
}