<?php

namespace Iwgb\Join\Domain;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @ORM\Column
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="\Iwgb\Join\Domain\UuidGenerator")
     */
    protected string $id;

    /**
     * @ORM\Column
     */
    protected string $session;

    /**
     * @ORM\Column(nullable = true)
     */
    protected ?string $plan;

    /**
     * @ORM\Column(nullable = true)
     */
    protected ?string $airtableId;

    /**
     * @ORM\Column(type="datetime")
     */
    protected DateTIme $timestamp;

    /**
     * @ORM\Column
     */
    protected bool $coreDataComplete = false;

    /**
     * @ORM\Column
     */
    protected bool $branchDataComplete = false;

    /**
     * @ORM\Column
     */
    protected bool $paymentComplete = false;

    /**
     * @ORM\OneToMany(targetEntity="\Iwgb\Join\Domain\Event", mappedBy="applicant")
     */
    protected Collection $events;

    /**
     * Applicant constructor.
     * @throws Exception
     */
    public function __construct() {
        $this->session = Uuid::uuid4();
        $this->timestamp = new DateTime();
        $this->events = new ArrayCollection();
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
     * @return bool
     */
    public function isPaymentComplete(): bool {
        return $this->paymentComplete;
    }

    /**
     * @param bool $paymentComplete
     */
    public function setPaymentComplete(bool $paymentComplete): void {
        $this->paymentComplete = $paymentComplete;
    }

    /**
     * @return array
     */
    public function getEvents() {
        return $this->events->toArray();
    }

    /**
     * @param Airtable $airtable
     * @return Record
     * @throws AirtableApiException
     */
    public function fetchRecord(Airtable $airtable): ?Record {

        if (!empty($this->airtableId)) {
            return $airtable->get('Members', $this->airtableId);
        } else {
            $record = $airtable->search('Members', 'Applicant ID', $this->id)
                          ->getRecords()[0] ?? null;

            if (empty($record)) {
                return null;
            }

            $this->airtableId = $record->getId();
            return $record;
        }
    }
}