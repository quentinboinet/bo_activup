<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubsetRepository")
 */
class Subset
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
    private $api_subset_id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization", inversedBy="subsets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organization;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Device", mappedBy="subset")
     */
    private $devices;

    public function __construct()
    {
        $this->devices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApiSubsetId(): ?string
    {
        return $this->api_subset_id;
    }

    public function setApiSubsetId(string $api_subset_id): self
    {
        $this->api_subset_id = $api_subset_id;

        return $this;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * @return Collection|Device[]
     */
    public function getDevices(): Collection
    {
        return $this->devices;
    }

    public function addDevice(Device $device): self
    {
        if (!$this->devices->contains($device)) {
            $this->devices[] = $device;
            $device->setSubset($this);
        }

        return $this;
    }

    public function removeDevice(Device $device): self
    {
        if ($this->devices->contains($device)) {
            $this->devices->removeElement($device);
            // set the owning side to null (unless already changed)
            if ($device->getSubset() === $this) {
                $device->setSubset(null);
            }
        }

        return $this;
    }
}
