<?php

namespace App\Entity;

use App\Repository\MiseEnAvantRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MiseEnAvantRepository::class)
 */
class MiseEnAvant
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
    private $dateCreation;

    /**
     * @ORM\Column(type="date")
     */
    private $dateLivraisonMiseEnAvant;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2)
     */
    private $prix;

    /**
     * @ORM\Column(type="integer")
     */
    private $niveau;

    /**
     * @ORM\ManyToOne (targetEntity="App\Entity\Produit")
     */
    private $produitMiseEnAvant;

    /**
     * @ORM\ManyToOne (targetEntity="App\Entity\Origine")
     */
    private $origine;

    /**
     * @ORM\ManyToOne (targetEntity="App\Entity\Colisage")
     */
    private $colisage;

    /**
     * @ORM\ManyToOne (targetEntity="App\Entity\RaisonMiseEnAvant")
     */
    private $raison;

    /**
     * @return mixed
     */
    public function getRaison()
    {
        return $this->raison;
    }

    /**
     * @param mixed $raison
     */
    public function setRaison($raison): void
    {
        $this->raison = $raison;
    }



    /**
     * @return mixed
     */
    public function getColisage()
    {
        return $this->colisage;
    }

    /**
     * @param mixed $colisage
     */
    public function setColisage($colisage): void
    {
        $this->colisage = $colisage;
    }


    /**
     * @return mixed
     */
    public function getOrigine()
    {
        return $this->origine;
    }

    /**
     * @param mixed $origine
     */
    public function setOrigine($origine): void
    {
        $this->origine = $origine;
    }




    /**
     * @return mixed
     */
    public function getProduitMiseEnAvant()
    {
        return $this->produitMiseEnAvant;
    }

    /**
     * @param mixed $produitMiseEnAvant
     */
    public function setProduitMiseEnAvant($produitMiseEnAvant): void
    {
        $this->produitMiseEnAvant = $produitMiseEnAvant;
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateLivraisonMiseEnAvant(): ?\DateTimeInterface
    {
        return $this->dateLivraisonMiseEnAvant;
    }

    public function setDateLivraisonMiseEnAvant(\DateTimeInterface $dateLivraisonMiseEnAvant): self
    {
        $this->dateLivraisonMiseEnAvant = $dateLivraisonMiseEnAvant;

        return $this;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getNiveau(): ?int
    {
        return $this->niveau;
    }

    public function setNiveau(int $niveau): self
    {
        $this->niveau = $niveau;

        return $this;
    }
}
