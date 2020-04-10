<?php

namespace Iwgb\Join\Log;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Iwgb\Join\Domain\Applicant;
use Iwgb\Join\Domain\Event;
use Monolog\Handler\AbstractProcessingHandler;

class ApplicantEventLogHandler extends AbstractProcessingHandler {

    private EntityManager $em;

    public function __construct(EntityManager $em, int $level) {
        parent::__construct($level, true);

        $this->em = $em;
    }

    /**
     * @inheritDoc
     * @throws ORMException
     */
    protected function write(array $record) {

        /** @var Applicant $applicant */
        $applicant = $record['extra']['applicant'] ?? null;

        if (!empty($applicant)) {

            $context = array_filter(
                $record['context'],
                fn(string $key): bool => substr($key, 0, 2) !== 'h_',
                ARRAY_FILTER_USE_KEY
            );

            $this->em->persist(new Event($applicant, $record['message'], $context));
        }
    }
}