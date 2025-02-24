<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Validator\Emailexist ;
use App\Validator\Passwordvalid ;
use App\Validator\Namevalid ;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[UniqueEntity(fields: ['email'], message: 'This email is already in use.')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50 )]
    #[Assert\NotBlank(message: "* First name is required.")]
    #[Assert\Length(max: 50, maxMessage: "First name cannot exceed 50 characters.")]
    #[Namevalid(message : '* Invalid Firstname !')]
    private ?string $firstName = null;

    #[ORM\Column(length: 50  )]
    #[Assert\NotBlank(message: "* Last name is required.")]
    #[Assert\Length(max: 50, maxMessage: "First name cannot exceed 50 characters.")]
    #[Namevalid(message : '* Invalid Lastname !')]
    private ?string $lastName = null;

    #[Assert\Length(max: 50, maxMessage: "* Address cannot exceed 50 characters.")]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $address = null;

    #[Assert\NotBlank(message: "* Email is required.")]
    #[Assert\Email(message: "* Invalid email format.")]
    #[Assert\Length(max: 50, maxMessage: "* Email cannot exceed 50 characters.")]
    #[ORM\Column(length: 50)]
    private ?string $email = null;

    #[Assert\NotBlank(message: "* Password is required.")]
    #[Assert\Length(min: 8, max: 100, minMessage: "* Password must be at least 8 characters long.")]
    #[ORM\Column(length: 100)]
    private ?string $password = null;

    #[Assert\Length(max: 100, maxMessage: "* bio cannot exceed 100 characters.")]
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: "* Tokens must be a positive number or zero.")]
    private ?int $tokens = null;

    #[ORM\Column]
    private ?int $role = null;

    #[ORM\Column(length: 100, nullable: true)]
    //#[Assert\Url(message: "* Picture must be a valid URL.")]
    private ?string $picture = null;

    
    // seperate form validation ( easy peasy )
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('email', new Assert\NotBlank([
            'groups' => ['signin'],
            'message' => 'Email is required!'
        ]));

        $metadata->addPropertyConstraint('email', new Emailexist('strict',['signin'], null));

        $metadata->addPropertyConstraint('email', new Assert\Email([
            'groups' => ['signin'],
            'message' => 'Please enter a valid email address.'
        ]));
        
        $metadata->addPropertyConstraint('password', new Assert\NotBlank([
            'groups' => ['signin'],
            'message' => 'Password is required!'
        ]));
        $metadata->addPropertyConstraint('password', new Assert\Length([
            'min' => 6,
            'minMessage' => 'Your password should be at least {{ limit }} characters.',
            'max' => 50,
            'maxMessage' => 'Your password should not be longer than {{ limit }} characters.',
            'groups' => ['signin']
        ]));

        $metadata->addPropertyConstraint('password', new Passwordvalid('email', 'strict', ['signin'], null));

    }

    /**
     * @var Collection<int, Bid>
     */
    #[ORM\OneToMany(targetEntity: Bid::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $bids;

    /**
     * @var Collection<int, Artwork>
     */
    #[ORM\OneToMany(targetEntity: Artwork::class, mappedBy: 'user')]
    private Collection $artworks;

    #[ORM\Column]
    private ?int $status = null;

    public function __construct()
    {
        $this->bids = new ArrayCollection();
        $this->artworks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }

    public function getTokens(): ?int
    {
        return $this->tokens;
    }

    public function setTokens(int $tokens): static
    {
        $this->tokens = $tokens;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(string $picture): static
    {
        $this->picture = $picture;

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
            $bid->setUser($this);
        }

        return $this;
    }

    public function removeBid(Bid $bid): static
    {
        if ($this->bids->removeElement($bid)) {
            // set the owning side to null (unless already changed)
            if ($bid->getUser() === $this) {
                $bid->setUser(null);
            }
        }

        return $this;
    }

    //required for implementing ! 
    public function eraseCredentials(): void
    {
        // Implement this method to clear any sensitive data
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_USER']; 
    
        if ($this->role === 1) {
            $roles[] = 'ROLE_ADMIN';
        }
    
        return $roles;
    }

    public function getUserIdentifier(): string
    {
        return $this->email; // Use email as the unique identifier
    }

    /**
     * @return Collection<int, Artwork>
     */
    public function getArtworks(): Collection
    {
        return $this->artworks;
    }

    public function addArtwork(Artwork $artwork): static
    {
        if (!$this->artworks->contains($artwork)) {
            $this->artworks->add($artwork);
            $artwork->setUser($this);
        }

        return $this;
    }

    public function removeArtwork(Artwork $artwork): void
    {
        if ($this->artworks->removeElement($artwork)) {
            // set the owning side to null (unless already changed)
            if ($artwork->getUser() === $this) {
                $artwork->setUser(null);
            }
        }
    }
    public function getRole(): ?int
    {
        return $this->role;
    }
    public function setRole(int $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

}
