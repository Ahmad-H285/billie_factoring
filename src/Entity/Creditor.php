<?php

namespace App\Entity;

use App\Repository\CreditorRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CreditorRepository::class)
 */
class Creditor
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $code;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $debtorLimit;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getDebtorLimit(): ?int
    {
        return $this->debtorLimit;
    }

    public function setDebtorLimit(?int $debtorLimit): self
    {
        $this->debtorLimit = $debtorLimit;

        return $this;
    }
}
