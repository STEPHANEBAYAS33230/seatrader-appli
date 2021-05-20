<?php

namespace App\Entity;

use App\Repository\CalendrierLivraisonRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CalendrierLivraisonRepository::class)
 */
class CalendrierLivraison
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $dateLivraison;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $OuverteBloque;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateLivraison(): ?\DateTimeInterface
    {
        return $this->dateLivraison;
    }

    public function setDateLivraison(\DateTimeInterface $dateLivraison): self
    {
        $this->dateLivraison = $dateLivraison;

        return $this;
    }

    public function getOuverteBloque(): ?string
    {
        return $this->OuverteBloque;
    }

    public function setOuverteBloque(string $OuverteBloque): self
    {
        $this->OuverteBloque = $OuverteBloque;

        return $this;
    }
}
