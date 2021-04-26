<?php

namespace App\Entity;

use App\Repository\FamilleProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=FamilleProduitRepository::class)
 * @UniqueEntity(
 *     fields={"nomFamille"},
 *     errorPath="nomFamille",
 *     message="cette famille existe déjà."
 * )
 */
class FamilleProduit
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $nomFamille;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Produit", mappedBy="famille", cascade={"persist","remove"})
     */
    private $listingProduits;

    /**
     * FamilleProduit constructor.
     * @param $listingProduits
     */
    public function __construct()
    {
        $this->listingProduits = new ArrayCollection();
    }
    //****************************************
    /**
     * @return Collection|Produit[]
     */
    public function getListingProduits(): Collection
    {
        return $this->listingProduits;
    }

    //ajouter un produit
    public function addProduit(Produit $listingProduits): self
    {
        if (!$this->listingProduits->contains($listingProduits)) {
            $this->listingProduits[] = $listingProduits;
            //$listingProduits->addSortie($this);
        }
        return $this;
    }

    //enlever un produit
    public function removeProduit(Produit $listingProduits): self
    {
        if ($this->listingProduits->contains($listingProduits)) {
            $this->listingProduits->removeElement($listingProduits);
            //$listingProduits->removeSortie($this);
        }
        return $this;
    }

    //****************************************

    /**
     * @return Collection|Produit[]
     */
    public function getListingsProduits(): Collection
    {
        return $this->listingProduits;
    }

    public function __toString()
    {
        return $this->id.' - '.$this->nomFamille;

    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomFamille(): ?string
    {
        return $this->nomFamille;
    }

    public function setNomFamille(string $nomFamille): self
    {
        $this->nomFamille = strtoupper($nomFamille);

        return $this;
    }
}
