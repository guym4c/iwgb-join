<?php

namespace Iwgb\Join\Handler\Api\Onboarding;

use Guym4c\Airtable\AirtableApiException;
use Guym4c\Airtable\ListFilter;
use Guym4c\Airtable\Sort;
use Iwgb\Join\Handler\RootHandler;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class PlanProxy extends RootHandler {

    /**
     * {@inheritdoc}
     * @throws AirtableApiException
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $plans = $this->airtable->list('Plans', (new ListFilter())
            ->addSort(new Sort('Name')))
            ->getRecords();

        $results = [];
        foreach ($plans as $plan) {
            $results[] = array_merge($plan->getData(), [
                'id' => $plan->getId(),
            ]);
        }

        return $response->withJson($results);
    }
}