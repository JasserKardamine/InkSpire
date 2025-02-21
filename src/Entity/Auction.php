<?php

namespace App\Entity;


use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AuctionRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AuctionRepository::class)]
class Auction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Label cannot be empty.')]

    private ?string $label = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\NotBlank(message: 'Start date is required.')]
    #[Assert\Type(type: '\DateTimeInterface', message: 'Invalid date format.')]
    #[Assert\GreaterThan("today", message: "The start date must be after today's date.")]
    private ?\DateTimeInterface $startDate = null;
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\NotBlank(message: 'End date is required.')]
    #[Assert\Type(type: '\DateTimeInterface', message: 'Invalid date format.')]
    #[Assert\GreaterThan(
        propertyPath: 'startDate',
        message: 'End date must be after the start date.'
    )]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Starting price is required.')]
    #[Assert\Positive(message: 'Starting price must be a positive number.')]
    private ?float $startPrice = null;

    #[ORM\Column]
    private ?float $endPrice = null;

    #[ORM\Column(length: 10)]
    private ?string $status = null;
    /**
     * @var Collection<int, Bid>
     */
    #[ORM\OneToMany(targetEntity: Bid::class, mappedBy: 'auction', orphanRemoval: true)]
    private Collection $bids;

    #[ORM\OneToOne(cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: 'Artwork is required.')]
    private ?Artwork $artwork = null;

    public function __construct()
    {
        $this->bids = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getStartPrice(): ?float
    {
        return $this->startPrice;
    }

    public function setStartPrice(?float $startPrice): static
    {
        $this->startPrice = $startPrice;

        return $this;
    }

    public function getEndPrice(): ?float
    {
        return $this->endPrice;
    }

    public function setEndPrice(?float $endPrice): static
    {
        $this->endPrice = $endPrice;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Bid>
     */
    public function getBids(): Collection
    {
        return $this->bids;
    }

    public function addBid(Bid $bid): static
    {
        if (!$this->bids->contains($bid)) {
            $this->bids->add($bid);
            $bid->setAuction($this);
        }

        return $this;
    }

    public function removeBid(Bid $bid): static
    {
        if ($this->bids->removeElement($bid)) {
            if ($bid->getAuction() === $this) {
                $bid->setAuction(null);
            }
        }

        return $this;
    }

    public function getArtwork(): ?Artwork
    {
        return $this->artwork;
    }

    public function setArtwork(Artwork $artwork): static
    {
        $this->artwork = $artwork;

        return $this;
    }
}
