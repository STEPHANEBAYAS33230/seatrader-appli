<?php

namespace App\Entity;

use App\Repository\IdentificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IdentificationRepository::class)
 */
class Identification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $nomDeLaSociete;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomDeLaSociete(): ?string
    {
        return $this->nomDeLaSociete;
    }

    public function setNomDeLaSociete(string $nomDeLaSociete): self
    {
        $this->nomDeLaSociete = $nomDeLaSociete;

        return $this;
    }
}
