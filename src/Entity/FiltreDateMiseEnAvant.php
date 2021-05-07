<?php

namespace App\Entity;

use App\Repository\FiltreDateMiseEnAvantRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FiltreDateMiseEnAvantRepository::class)
 */
class FiltreDateMiseEnAvant
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateMeA;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $datePlus;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateMeA(): ?\DateTimeInterface
    {
        return $this->dateMeA;
    }

    public function setDateMeA(?\DateTimeInterface $dateMeA): self
    {
        $this->dateMeA = $dateMeA;

        return $this;
    }

    public function getDatePlus(): ?\DateTimeInterface
    {
        return $this->datePlus;
    }

    public function setDatePlus(?\DateTimeInterface $datePlus): self
    {
        $this->datePlus = $datePlus;

        return $this;
    }
}
