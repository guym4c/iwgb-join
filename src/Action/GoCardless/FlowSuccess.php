<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace IWGB\Join\Action\GoCardless;

use GoCardlessPro\Core\Exception\InvalidStateException;
use GoCardlessPro\Resources\RedirectFlow;
use Guym4c\Airtable\AirtableApiException;
use IWGB\Join\Domain\Applicant;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class FlowSuccess extends GenericGoCardlessAction {

    const FLOW_ID_PARAM_KEY = 'redirect_flow_id';

    /**
     * {@inheritdoc}
     * @throws AirtableApiException
     * @throws InvalidStateException
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        if (empty($request->getQueryParam(self::FLOW_ID_PARAM_KEY)))
            return $response->withRedirect(self::INVALID_INPUT_RETURN_URL);

        $flow = $this->gocardless->redirectFlows()
            ->get($request->getQueryParam(self::FLOW_ID_PARAM_KEY));

        /** @var Applicant $applicant */
        $applicant = $this->em->getRepository(Applicant::class)
            ->findOneBy(['session' => $flow->session_token]);
        $record = $applicant->fetchRecord($this->airtable);

        $flow = $this->gocardless->redirectFlows()
            ->complete($flow->id, $applicant->getSession());

        $record->{'Customer ID'} = $flow->links['customer'];
        $this->airtable->update($record);

        $plan = $this->airtable->get('Plans', $applicant->getMembershipType());







    }
}