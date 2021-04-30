<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProduitRepository::class)
 * @UniqueEntity(
 *     fields={"nomProduit"},
 *     errorPath="nomProduit",
 *     message="ce produit existe déjà"
 * )
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
     * @ORM\Column(type="string", length=15)
     */
    private $nomProduit;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
     */
    private $quantite=0;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\FamilleProduit", inversedBy="listingProduits")
     */
    private $famille;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $pieceOuKg;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Assert\NotBlank(message="Please, upload the product brochure as a PNG file.")
     * @Assert\File(mimeTypes={ "image/png", "image/jpeg", "image/jpg", "image/gif"})
     */
    private $brochureFilename;

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
        $this->nomProduit = ucwords(strtolower($nomProduit));

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

    public function getBrochureFilename(): ?string
    {
        return $this->brochureFilename;
    }

    public function setBrochureFilename(?string $brochureFilename): self
    {
        $this->brochureFilename = $brochureFilename;

        return $this;
    }
}
