<?php

namespace Iwgb\Join\Domain;

use Guym4c\Airtable\Loader;
use Guym4c\Airtable\Record;

class AirtablePlanRecord {


    private string $branch;

    private string $plan;

    private int $amount;

    private int $interval;

    private string $intervalUnit;

    private int $dayOfMonth;

    public function __construct(Record $record) {
        foreach (get_object_vars($this) as $property => $value) {

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
        $property = preg_replace_callback('/([A-Z])/', function (array $matches): string {
            return ' ' . strtolower($matches[0]);
        }, $property);
        return strtoupper(substr($property, 0, 1)) . substr($property, 1);
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