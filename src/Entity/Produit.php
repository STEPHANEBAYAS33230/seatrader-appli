<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProduitRepository::class)
 */
class Produit
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
    private $nomProduit;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
     */
    private $quantite;

    /**
     * @ORM\ManyToOne (targetEntity="App\Entity\FamilleProduit", inversedBy="listingProduits")
     */
    private $famille;

    /**
     * @ORM\ManyToOne (targetEntity="App\Entity\PieceOuKg")
     */
    private $pieceOuKg;

    /**
     * @return mixed
     */
    public function getPieceOuKg()
    {
        return $this->pieceOuKg;
    }

    /**
     * @param mixed $pieceOuKg
     */
    public function setPieceOuKg($pieceOuKg): void
    {
        $this->pieceOuKg = $pieceOuKg;
    }

    /**
     * @return mixed
     */
    public function getFamille()
    {
        return $this->famille;
    }

    /**
     * @param mixed $famille
     */
    public function setFamille($famille): void
    {
        $this->famille = $famille;
    }





    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomProduit(): ?string
    {
        return $this->nomProduit;
    }

    public function setNomProduit(string $nomProduit): self
    {
        $this->nomProduit = $nomProduit;

        return $this;
    }

    public function getQuantite(): ?string
    {
        return $this->quantite;
    }

    public function setQuantite(?string $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }
}
