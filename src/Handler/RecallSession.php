<?php

namespace Iwgb\Join\Handler;

use Aura\Session\Segment;
use Doctrine\ORM;
use Iwgb\Join\Domain\Applicant;
use Iwgb\Join\Handler\Api\Error\Error;
use Iwgb\Join\Handler\GoCardless\CompletePayment;
use Iwgb\Join\Middleware\ApplicantSession;
use Iwgb\Join\Route;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class RecallSession extends AbstractSessionValidationHandler {

    private Segment $session;

    public function __construct(Container $c) {
        parent::__construct($c);

        $this->session = $this->getSession();
    }

    /**
     * {@inheritDoc}
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     * @throws ORM\TransactionRequiredException
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $aid = $this->session->get(ApplicantSession::APPLICANT_ID);

        if (
            !$this->validate()
            || empty($aid)
        ) {
            return $this->errorRedirect($request, $response,
                Error::SESSION_START_FAILED()
            );
        }

        /** @var Applicant $applicant */
        $applicant = $this->em->find(Applicant::class, $aid);

        if (empty($applicant)) {
            return $this->errorRedirect($request, $response,
                Error::RECALLED_APPLICANT_INVALID()
            );
        }

        ApplicantSession::initialise($this->session, $applicant);

        if ($applicant->isPaymentComplete()) {
            return $response->withRedirect(CompletePayment::CONFIRMATION_REDIRECT_URL);
        }

        if ($applicant->isBranchDataComplete()) {
            return $this->redirectToRoute($response, Route::COMPLETE_PAYMENT);
        }

        if ($applicant->isCoreDataComplete()) {
            return $this->redirectToRoute($response, Route::BRANCH_DATA);
        }

        if (!empty($applicant->getPlan())) {
            return $this->redirectToRoute($response, Route::CORE_DATA);
        }

        return $this->errorRedirect($request, $response,
            Error::RECALLED_APPLICATION_NOT_STARTED()
        );

    }
}