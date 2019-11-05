<?php

namespace IWGB\Join\Domain;

use Doctrine\ORM\Mapping as ORM;
use GraphQL\Doctrine\Annotation as API;
use Guym4c\Airtable\Airtable;
use Guym4c\Airtable\AirtableApiException;
use Guym4c\Airtable\Record;

/**
 * @ORM\Entity
 */
class SorterResult {

    /**
     * @var string
     *
     * @ORM\Column
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="\IWGB\Join\Domain\UuidGenerator")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column
     */
    protected $form;

    /**
     * @var string
     *
     * @ORM\Column
     */
    protected $question;

    /**
     * @var string
     *
     * @ORM\Column
     */
    protected $conditional;

    /**
     * @var string
     *
     * @ORM\Column
     */
    protected $plan;

    /**
     * SorterResult constructor.
     * @param string $description
     * @param string $form
     * @param string $question
     * @param string $conditional
     * @param string $plan
     */
    public function __construct(string $description, string $form, string $question, string $conditional, string $plan) {
        $this->description = $description;
        $this->form = $form;
        $this->question = $question;
        $this->conditional = $conditional;
        $this->plan = $plan;
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
    public function getDescription(): string {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getForm(): string {
        return $this->form;
    }

    /**
     * @param string $form
     */
    public function setForm(string $form): void {
        $this->form = $form;
    }

    /**
     * @return string
     */
    public function getQuestion(): string {
        return $this->question;
    }

    /**
     * @param string $question
     */
    public function setQuestion(string $question): void {
        $this->question = $question;
    }

    /**
     * @return string
     */
    public function getConditional(): string {
        return $this->conditional;
    }

    /**
     * @param string $conditional
     */
    public function setConditional(string $conditional): void {
        $this->conditional = $conditional;
    }

    /**
     * @return string
     */
    public function getPlan(): string {
        return $this->plan;
    }

    /**
     * @param string $plan
     */
    public function setPlan(string $plan): void {
        $this->plan = $plan;
    }

    /**
     * @API\Exclude
     *
     * @param Airtable $airtable
     * @return Record
     * @throws AirtableApiException
     */
    public function fetchPlan(Airtable $airtable): Record {
        return $airtable->get('Plans', $this->plan);
    }
}