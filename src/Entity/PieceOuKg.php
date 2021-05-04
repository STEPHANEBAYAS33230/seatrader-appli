<?php

namespace App\Entity;

use App\Repository\PieceOuKgRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PieceOuKgRepository::class)
 */
class PieceOuKg
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $mesure;

    public function __toString()
    {
        return $this->mesure;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMesure(): ?string
    {
        return $this->mesure;
    }

    public function setMesure(string $mesure): self
    {
        $this->mesure = $mesure;

        return $this;
    }
}
