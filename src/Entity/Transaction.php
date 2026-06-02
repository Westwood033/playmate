<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $transactedAt = null;

    #[ORM\OneToOne(inversedBy: 'transaction', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Item $item = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $buyer = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $billingAddress = null;

    #[ORM\Column]
    private ?bool $sameAddress = null;

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransactedAt(): ?\DateTimeImmutable
    {
        return $this->transactedAt;
    }

    public function setTransactedAt(\DateTimeImmutable $transactedAt): static
    {
        $this->transactedAt = $transactedAt;

        return $this;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(Item $item): static
    {
        $this->item = $item;

        return $this;
    }

    public function getBuyer(): ?User
    {
        return $this->buyer;
    }

    public function setBuyer(?User $buyer): static
    {
        $this->buyer = $buyer;

        return $this;
    }

    public function getBillingAddress(): ?string
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(string $billingAddress): static
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    public function isSameAddress(): ?bool
    {
        return $this->sameAddress;
    }

    public function setSameAddress(bool $sameAddress): static
    {
        $this->sameAddress = $sameAddress;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }
}
