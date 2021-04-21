<?php

namespace App\Entity;

use App\Repository\EtatUtilisateurRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EtatUtilisateurRepository::class)
 */
class EtatUtilisateur
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $etatUtilisateur;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEtatUtilisateur(): ?string
    {
        return $this->etatUtilisateur;
    }

    public function setEtatUtilisateur(string $etatUtilisateur): self
    {
        $this->etatUtilisateur = $etatUtilisateur;

        return $this;
    }
}
