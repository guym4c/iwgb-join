<?php

namespace Iwgb\Join\Middleware;

use Aura\Session\Segment;
use Aura\Session\Session as SessionManager;
use Doctrine\ORM;
use Doctrine\ORM\EntityManager;
use Iwgb\Join\Domain\Applicant;
use Iwgb\Join\Handler\Api\Error\Error;
use Iwgb\Join\Handler\Api\Error\ErrorHandler;
use Iwgb\Join\Log\ApplicantEventLogProcessor;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Sentry;
use Sentry\State\Scope;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class ApplicantSession extends AbstractMiddleware {

    private const INVALID_SESSION_RETURN_URL = 'https://iwgb.org.uk/join?session=invalid';
    public const APPLICANT_ID = 'aid';
    public const USER_AGENT = 'userAgent';

    private SessionManager $sm;

    private Segment $session;

    private EntityManager $em;

    private Logger $log;

    public function __construct(Container $c) {
        parent::__construct($c);

        $this->sm = $this->c['session'];
        $this->session = $this->sm->getSegment(self::class);
        $this->em = $this->c['em'];
        $this->log = $this->c['log'];
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param callable $next
     * @return ResponseInterface
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     * @throws ORM\TransactionRequiredException
     */
    public function __invoke(Request $request, Response $response, callable $next): ResponseInterface {

        if ($this->session->get(self::USER_AGENT) !== $_SERVER['HTTP_USER_AGENT'] ?? null) {
            return ErrorHandler::redirect($response, $this->sm,
                Error::CSRF_USER_AGENT_MISMATCH()
            );
        }

        $aid = $this->session->get(self::APPLICANT_ID);

        if (empty($aid)) {
            return ErrorHandler::redirect($response, $this->sm,
                Error::APPLICANT_INVALID()
            );
        }

        /** @var Applicant $applicant */
        $applicant = $this->em->find(Applicant::class, $aid);

        if (empty($applicant)) {
            return ErrorHandler::redirect($response, $this->sm,
                Error::APPLICANT_INVALID()
            );
        }

        Sentry\configureScope(function (Scope $scope) use ($applicant): void {
            $scope->setUser(['id' => $applicant->getId()]);
        });

        $this->log->pushProcessor(new ApplicantEventLogProcessor($applicant));

        return $next($request->withAttribute('applicant', $applicant), $response);
    }

    public static function sessionInvalid(Response $response, SessionManager $sm): ResponseInterface {
        $sm->destroy();
        return $response->withRedirect(self::INVALID_SESSION_RETURN_URL);
    }

    public static function initialise(Segment $session, Applicant $applicant): void {
        $session->set(self::APPLICANT_ID, $applicant->getId());
        $session->set(self::USER_AGENT, $_SERVER['HTTP_USER_AGENT']);
    }
}