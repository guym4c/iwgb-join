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
class Applicant extends AbstractAirtableMember {

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

    protected $airtableId;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $timestamp;

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
}