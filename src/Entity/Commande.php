<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=CommandeRepository::class)
 */
class Commande
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
    private $jourDeLivraison;

    /**
     * @ORM\Column(type="date")
     */
    private $dateCreationCommande;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $note;

    /**
     * @ORM\ManyToOne (targetEntity="App\Entity\EtatCommande")
     */
    private $etatCommande;

    /**
     * @ORM\ManyToOne (targetEntity="App\Entity\Utilisateur")
     */
    private $utilisateur;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Produit", mappedBy="commande")
     */
    private $listeProduits;

    /**
     * @ORM\Column(type="object", nullable=true)
     */
    private $object;

    /**
     * Commande constructor.
     * @param $listeProduits
     */
    public function __construct()
    {

        $this->listeProduits = new ArrayCollection();

    }

    /**
     * @return Collection|Produit[]
     */
    public function getListeProduits(): Collection
    {
        return $this->listeProduits;
    }

    /**
     * @param ArrayCollection $listeProduits
     */
    public function setListeProduits(ArrayCollection $listeProduits): void
    {
        $this->listeProduits = $listeProduits;
    }


    //ajouter un produit
    public function add (Produit $listeProduits): self
    {
        if (!$this->listeProduits->contains($listeProduits)) {
            $this->listeProduits[] = $listeProduits;
            //$listingProduits->addSortie($this);
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEtatCommande()
    {
        return $this->etatCommande;
    }

    /**
     * @param mixed $etatCommande
     */
    public function setEtatCommande($etatCommande): void
    {
        $this->etatCommande = $etatCommande;
    }

    /**
     * @return mixed
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * @param mixed $utilisateur
     */
    public function setUtilisateur($utilisateur): void
    {
        $this->utilisateur = $utilisateur;
    }

   //manque methode ajouter produit remove produit de la listeProduits



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJourDeLivraison(): ?\DateTimeInterface
    {
        return $this->jourDeLivraison;
    }

    public function setJourDeLivraison(\DateTimeInterface $jourDeLivraison): self
    {
        $this->jourDeLivraison = $jourDeLivraison;

        return $this;
    }

    public function getDateCreationCommande(): ?\DateTimeInterface
    {
        return $this->dateCreationCommande;
    }

    public function setDateCreationCommande(\DateTimeInterface $dateCreationCommande): self
    {
        $this->dateCreationCommande = $dateCreationCommande;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function setObject($object): self
    {
        $this->object = $object;

        return $this;
    }




}
