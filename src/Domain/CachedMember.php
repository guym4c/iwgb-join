<?php
/** @noinspection PhpUndefinedFieldInspection */


namespace IWGB\Join\Domain;

use Doctrine\ORM\Mapping as ORM;
use Guym4c\Airtable\Record;

/**
 * @ORM\Entity
 */
class CachedMember extends AbstractAirtableMember {

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
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column
     */
    protected $phone;

    /**
     * @var array
     *
     * @ORM\Column
     */
    protected $workplaces = [];

    /**
     * @var array
     *
     * @ORM\Column
     */
    protected $employers = [];

    /**
     * @var string
     *
     * @ORM\Column
     */
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column
     */
    protected $actionNetworkId;

    public static function fromAirtable(Record $record): self {
        $member = new self();

        $member->setEmail($record->Email);
        $member->setPhone($record->Mobile);
        $member->setWorkplaces(self::parseNameArray($record, 'Workplace', 'Workplaces'));
        $member->setEmployers(self::parseNameArray($record, 'Employer', 'Employers'));
        $member->setActionNetworkId($record->{'ActionNetwork ID'});

        return $member;
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
    public function getEmail(): string {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPhone(): string {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone(string $phone): void {
        $this->phone = $phone;
    }

    /**
     * @return array
     */
    public function getWorkplaces(): array {
        return $this->workplaces;
    }

    /**
     * @param array $workplaces
     */
    public function setWorkplaces(array $workplaces): void {
        $this->workplaces = $workplaces;
    }

    /**
     * @return array
     */
    public function getEmployers(): array {
        return $this->employers;
    }

    /**
     * @param array $employers
     */
    public function setEmployers(array $employers): void {
        $this->employers = $employers;
    }

    /**
     * @return string
     */
    public function getStatus(): string {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getActionNetworkId(): string {
        return $this->actionNetworkId;
    }

    /**
     * @param string $actionNetworkId
     */
    public function setActionNetworkId(string $actionNetworkId): void {
        $this->actionNetworkId = $actionNetworkId;
    }

    private static function parseNameArray(Record $record, string $field, string $targetTable): array {

        $names = [];
        if (!self::isEmpty($record->$field)) {

            $records = $record->load($field, $targetTable);

            if (is_array($records)) {
                foreach ($records as $record) {
                    $names[] = $record->Name;
                }
            } else {
                $names[] = $record->Name;
            }
        }

        return $names;
    }

    private static function isEmpty($field): bool {
        if (is_array($field) &&
            count($field) == 1) {

                return empty($field[0]);
        }
        return empty($field);
    }
}