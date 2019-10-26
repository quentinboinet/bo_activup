<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DeviceRepository")
 */
class Device
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
     * @ORM\OneToMany(targetEntity="App\Entity\Ping", mappedBy="device")
     */
    private $Pings;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="devices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $client;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $api_sensorId;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\WeeklyStat", mappedBy="device")
     */
    private $weeklyStats;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DailyStat", mappedBy="device")
     */
    private $dailyStats;

    public function __construct()
    {
        $this->Pings = new ArrayCollection();
        $this->weeklyStats = new ArrayCollection();
        $this->dailyStats = new ArrayCollection();
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
     * @return Collection|Ping[]
     */
    public function getPings(): Collection
    {
        return $this->Pings;
    }

    public function addPing(Ping $ping): self
    {
        if (!$this->Pings->contains($ping)) {
            $this->Pings[] = $ping;
            $ping->setDevice($this);
        }

        return $this;
    }

    public function removePing(Ping $ping): self
    {
        if ($this->Pings->contains($ping)) {
            $this->Pings->removeElement($ping);
            // set the owning side to null (unless already changed)
            if ($ping->getDevice() === $this) {
                $ping->setDevice(null);
            }
        }

        return $this;
    }

    public function getClient(): ?client
    {
        return $this->client;
    }

    public function setClient(?client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getApiSensorId(): ?string
    {
        return $this->api_sensorId;
    }

    public function setApiSensorId(?string $api_sensorId): self
    {
        $this->api_sensorId = $api_sensorId;

        return $this;
    }

    /**
     * @return Collection|WeeklyStat[]
     */
    public function getWeeklyStats(): Collection
    {
        return $this->weeklyStats;
    }

    public function addWeeklyStat(WeeklyStat $weeklyStat): self
    {
        if (!$this->weeklyStats->contains($weeklyStat)) {
            $this->weeklyStats[] = $weeklyStat;
            $weeklyStat->setDevice($this);
        }

        return $this;
    }

    public function removeWeeklyStat(WeeklyStat $weeklyStat): self
    {
        if ($this->weeklyStats->contains($weeklyStat)) {
            $this->weeklyStats->removeElement($weeklyStat);
            // set the owning side to null (unless already changed)
            if ($weeklyStat->getDevice() === $this) {
                $weeklyStat->setDevice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|DailyStat[]
     */
    public function getDailyStats(): Collection
    {
        return $this->dailyStats;
    }

    public function addDailyStat(DailyStat $dailyStat): self
    {
        if (!$this->dailyStats->contains($dailyStat)) {
            $this->dailyStats[] = $dailyStat;
            $dailyStat->setDevice($this);
        }

        return $this;
    }

    public function removeDailyStat(DailyStat $dailyStat): self
    {
        if ($this->dailyStats->contains($dailyStat)) {
            $this->dailyStats->removeElement($dailyStat);
            // set the owning side to null (unless already changed)
            if ($dailyStat->getDevice() === $this) {
                $dailyStat->setDevice(null);
            }
        }

        return $this;
    }
}
