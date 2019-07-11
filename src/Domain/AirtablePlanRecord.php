<?php

namespace IWGB\Join\Domain;

use Guym4c\Airtable\Loader;
use Guym4c\Airtable\Record;

class AirtablePlanRecord {

    /** @var string */
    private $branch;

    /** @var string */
    private $plan;

    /** @var int */
    private $amount;

    /** @var int */
    private $interval;

    /** @var string */
    private $intervalUnit;

    /** @var int */
    private $dayOfMonth;

    public function __construct(Record $record) {
        foreach (get_object_vars($this) as $property) {

            if (empty($this->{$property})) {
                $field = $record->{$this->filterPropertyName($property)};

                if ($field instanceof Loader) {
                    $field = $field->getId();
                }
                $this->{$property} = $field;
            }
        }
    }

    private function filterPropertyName(string $property) {
        return preg_replace_callback('/([A-Z])/', function ($matches): array {
            for ($i = 0; $i < count($matches); $i++) {
                $matches[$i] = ' ' . strtolower($matches[$i]);
            }
            return $matches;
        }, $property);
    }

    public function getGoCardlessIntervalFormat(): array {

        switch ($this->intervalUnit) {
            case 'Weekly':
                return [
                    'interval_unit' => 'weekly',
                    'interval' => $this->interval,
                ];

            case 'Monthly':
                return [
                    'interval_unit' => 'monthly',
                    'interval' => $this->interval,
                    'day_of_month' => $this->dayOfMonth,
                ];

            case 'Annually':
                return [
                    'interval_unit' => 'yearly',
                    'interval' => 1,
                ];
        }

        return [];
    }

    /**
     * @return string
     */
    public function getPlanName(): string {
        return $this->plan;
    }

    /**
     * @return int
     */
    public function getAmount(): int {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getBranchId(): string {
        return $this->branch;
    }
}