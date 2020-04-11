<?php

namespace Iwgb\Join\Domain;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Event {

    public static string $prefix = 'EV';

    /**
     * @ORM\Column(name="id", length=15)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="\Iwgb\Join\Domain\PrefixedUniqidGenerator")
     */
    protected string $identifier;

    /**
     * @ORM\ManyToOne(targetEntity="\Iwgb\Join\Domain\Applicant", inversedBy="events")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    protected Applicant $applicant;

    /**
     * @ORM\Column
     */
    protected string $type;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected DateTime $timestamp;

    /**
     * @ORM\Column(type="json")
     */
    protected array $context;

    public function __construct(Applicant $applicant, string $type, array $context = []) {
        $this->applicant = $applicant;
        $this->type = $type;
        $this->timestamp = new DateTime();
        $this->context = $context;
    }
}