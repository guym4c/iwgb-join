<?php


namespace IWGB\Join\Domain;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
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
     * @ORM\GeneratedValue(strategy="UUID")
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
    protected $membershipType;

    /**
     * @var DateTime
     *
     * @ORM\Column
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
    public function getMembershipType(): ?string {
        return $this->membershipType;
    }

    /**
     * @param string|null $membershipType
     */
    public function setMembershipType(?string $membershipType): void {
        $this->membershipType = $membershipType;
    }
}