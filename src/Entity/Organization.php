<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrganizationRepository")
 */
class Organization
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Subset", mappedBy="organization")
     */
    private $subsets;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="organization")
     */
    private $users;

    public function __construct()
    {
        $this->subsets = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

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

    /**
     * @return Collection|Subset[]
     */
    public function getSubsets(): Collection
    {
        return $this->subsets;
    }

    public function addSubset(Subset $subset): self
    {
        if (!$this->subsets->contains($subset)) {
            $this->subsets[] = $subset;
            $subset->setOrganization($this);
        }

        return $this;
    }

    public function removeSubset(Subset $subset): self
    {
        if ($this->subsets->contains($subset)) {
            $this->subsets->removeElement($subset);
            // set the owning side to null (unless already changed)
            if ($subset->getOrganization() === $this) {
                $subset->setOrganization(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setOrganization($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getOrganization() === $this) {
                $user->setOrganization(null);
            }
        }

        return $this;
    }
}
