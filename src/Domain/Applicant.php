<?php


namespace IWGB\Join\Domain;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Guym4c\Airtable\Airtable;
use Guym4c\Airtable\AirtableApiException;
use Guym4c\Airtable\Record;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity
 */
class Applicant {

    /**
     * @var string
     *
     * @ORM\Column
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="\IWGB\Join\Domain\UuidGenerator")
     */
    protected $id;

    /** @var string
     *
     * @ORM\Column
     */
    protected $session;

    /**
     * @var ?string
     *
     * @ORM\Column(nullable = true)
     */
    protected $branch;

    /**
     * @var ?string
     *
     * @ORM\Column(nullable = true)
     */
    protected $plan;

    /**
     * @var ?string
     *
     * @ORM\Column(nullable = true)
     */
    protected $airtableId;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $timestamp;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $coreDataComplete = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $branchDataComplete = false;

    /**
     * Applicant constructor.
     * @throws Exception
     */
    public function __construct() {
        $this->session = Uuid::uuid4();
        $this->timestamp = new DateTime();
    }

    /**
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSession(): string {
        return $this->session;
    }

    /**
     * @return DateTime
     */
    public function getTimestamp(): DateTime {
        return $this->timestamp;
    }

    /**
     * @return string|null
     */
    public function getBranch(): ?string {
        return $this->branch;
    }

    /**
     * @param string|null $branch
     */
    public function setBranch(?string $branch): void {
        $this->branch = $branch;
    }

    /**
     * @return string|null
     */
    public function getPlan(): ?string {
        return $this->plan;
    }

    /**
     * @param string|null $plan
     */
    public function setPlan(?string $plan): void {
        $this->plan = $plan;
    }

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
     * @return bool
     */
    public function isCoreDataComplete(): bool {
        return $this->coreDataComplete;
    }

    /**
     * @param bool $coreDataComplete
     */
    public function setCoreDataComplete(bool $coreDataComplete): void {
        $this->coreDataComplete = $coreDataComplete;
    }

    /**
     * @return bool
     */
    public function isBranchDataComplete(): bool {
        return $this->branchDataComplete;
    }

    /**
     * @param bool $branchDataComplete
     */
    public function setBranchDataComplete(bool $branchDataComplete): void {
        $this->branchDataComplete = $branchDataComplete;
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
            $record = $airtable->search('Members', 'Applicant ID', $this->id)
                          ->getRecords()[0];
            $this->airtableId = $record->getId();
            return $record;
        }
    }
}