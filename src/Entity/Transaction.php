<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?DateTimeImmutable $transactedAt = null;

    #[ORM\OneToOne(inversedBy: 'transaction', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Item $item = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $buyer = null;

<<<<<<< HEAD
    #[ORM\ManyToOne(inversedBy: 'transactionsSold')]
=======
    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
>>>>>>> historique_achats
    private ?User $seller = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $billingAddress = null;

    #[ORM\Column(nullable: false, options: ['default' => false])]
    private bool $sameAddress = false;

    #[ORM\Column(length: 255)]
    private ?string $buyerAddress = null;

    #[ORM\Column(length: 255)]
    private ?string $sellerAddress = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransactedAt(): ?DateTimeImmutable
    {
        return $this->transactedAt;
    }

    public function setTransactedAt(DateTimeImmutable $transactedAt): static
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

    public function getSeller(): ?User
    {
        return $this->seller;
    }

    public function setSeller(?User $seller): static
    {
        $this->seller = $seller;

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

    public function getBuyerAddress(): ?string
    {
        return $this->buyerAddress;
    }

    public function setBuyerAddress(string $address): static
    {
        $this->buyerAddress = $address;

        return $this;
    }

    public function getSellerAddress(): ?string
    {
        return $this->sellerAddress;
    }

    public function setSellerAddress(string $address): static
    {
        $this->sellerAddress = $address;

        return $this;
    }

    public function getSeller(): ?User
    {
        return $this->seller;
    }

    public function setSeller(?User $seller): static
    {
        $this->seller = $seller;

        return $this;
    }
}
