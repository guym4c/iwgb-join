<?php

namespace IWGB\Join\Domain;

use Guym4c\Airtable\Airtable;
use Guym4c\Airtable\AirtableApiException;
use Guym4c\Airtable\Record;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractAirtableMember {

    /**
     * @var ?string
     *
     * @ORM\Column(nullable = true)
     */
    protected $airtableId;

    abstract public function getId(): string;

    /**
     * @return string|null
     */
    public function getAirtableId(): ?string {
        return $this->airtableId;
    }

    /**
     * @param ?string $airtableId
     */
    public function setAirtableId($airtableId): void {
        $this->airtableId = $airtableId;
    }

    /**
     * @param Airtable $airtable
     * @return Record
     * @throws AirtableApiException
     */
    public function fetchRecord(Airtable $airtable): Record {

        if (!empty($this->airtableId)) {
            return $airtable->get('Members', $this->airtableId);
        } else {
            $record = $airtable->search('Members', 'Applicant ID', $this->getId())
                          ->getRecords()[0];
            $this->airtableId = $record->getId();
            return $record;
        }
    }
}