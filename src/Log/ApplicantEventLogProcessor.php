<?php

namespace Iwgb\Join\Log;

use Iwgb\Join\Domain\Applicant;
use Monolog\Processor\ProcessorInterface;

class ApplicantEventLogProcessor implements ProcessorInterface {

    private Applicant $applicant;

    public function __construct(Applicant $applicant) {
        $this->applicant = $applicant;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(array $record): array {
        $record['extra']['applicant'] = $this->applicant;
        return $record;
    }
}