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
     * @ORM\Column(type="string")
     */
    private $etatUtilisateur;

    /**
     * EtatUtilisateur constructor.
     * @param $etatUtilisateur
     */
    public function __construct($etatUtilisateur)
    {
        $this->etatUtilisateur = $etatUtilisateur;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEtatUtilisateur(): ?string
    {
        return $this->etatUtilisateur;
    }

    public function __toString(){
        return $this->etatUtilisateur;
    }

    public function setEtatUtilisateur(string $etatUtilisateur): self
    {
        $this->etatUtilisateur = $etatUtilisateur;

        return $this;
    }
}
