<?php

namespace App\Entity;

use App\Repository\ColisageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ColisageRepository::class)
 */
class Colisage
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $nombreColisage;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreColisage(): ?int
    {
        return $this->nombreColisage;
    }

    public function setNombreColisage(int $nombreColisage): self
    {
        $this->nombreColisage = $nombreColisage;

        return $this;
    }
}
