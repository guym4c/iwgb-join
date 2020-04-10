<?php

namespace Iwgb\Join\Domain;

use Doctrine\ORM\Mapping as ORM;
use GraphQL\Doctrine\Annotation as API;
use GraphQL\Doctrine\Helper\GraphQLEntity;
use Guym4c\Airtable\Airtable;
use Guym4c\Airtable\AirtableApiException;
use Guym4c\Airtable\Record;

/**
 * @ORM\Entity
 */
class SorterResult extends GraphQLEntity {

    /**
     * @ORM\Column(name="id")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="\Iwgb\Join\Domain\UuidGenerator")
     */
    protected string $identifier;

    /**
     * @ORM\Column
     */
    protected string $friendlyName;

    /**
     * @ORM\Column
     */
    protected string $form;

    /**
     * @ORM\Column
     */
    protected string $question;

    /**
     * @ORM\Column
     */
    protected string $conditional;

    /**
     * @ORM\Column
     */
    protected string $plan;

    /**
     * SorterResult constructor.
     * @param string $friendlyName
     * @param string $form
     * @param string $question
     * @param string $conditional
     * @param string $plan
     * @return SorterResult
     */
    public static function construct(string $friendlyName, string $form, string $question, string $conditional, string $plan): self {
        $sorterResult = new self();
        $sorterResult->friendlyName = $friendlyName;
        $sorterResult->form = $form;
        $sorterResult->question = $question;
        $sorterResult->conditional = $conditional;
        $sorterResult->plan = $plan;
        return $sorterResult;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getFriendlyName(): string {
        return $this->friendlyName;
    }

    /**
     * @param string $friendlyName
     */
    public function setFriendlyName(string $friendlyName): void {
        $this->friendlyName = $friendlyName;
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