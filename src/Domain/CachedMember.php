<?php


namespace IWGB\Join\Domain;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CachedMember {

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
     * @var ?string
     *
     * @var\Column(nullable = true)
     */
    protected $workplace;

    /**
     * @var ?string
     *
     * @var\Column(nullable = true)
     */
    protected $employer;

    /**
     * @var string
     *
     * @var\Column
     */
    protected $status;

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
     * @return mixed
     */
    public function getWorkplace() {
        return $this->workplace;
    }

    /**
     * @param mixed $workplace
     */
    public function setWorkplace($workplace): void {
        $this->workplace = $workplace;
    }

    /**
     * @return mixed
     */
    public function getEmployer() {
        return $this->employer;
    }

    /**
     * @param mixed $employer
     */
    public function setEmployer($employer): void {
        $this->employer = $employer;
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
}