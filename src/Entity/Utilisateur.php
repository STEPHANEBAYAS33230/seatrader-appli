<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=UtilisateurRepository::class)
 * @UniqueEntity(
 *     fields={"nomDeLaSociete"},
 *     errorPath="nomDeLaSociete",
 *     message="ce nom de société existe déjà"
 * )
 */
class Utilisateur implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     * @Assert\NotBlank
     * @Assert\Type("string")
     */
    private $nomDeLaSociete;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = ['ROLE_ADMIN'];

    /**
     * @Assert\Regex(
     * pattern = "/^(?=.*\d)(?=.*[A-Z])(?=.*[@#$%])(?!.*(.)\1{2}).*[a-z]/m",
     * match=true,
     * message="Votre mot de passe doit comporter au moins huit caractères, dont des lettres majuscules et minuscules, un chiffre et un caractère spécial.")
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank
     * @Assert\Type("string")
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank
     * @Assert\Type("string")
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=55)
     * @Assert\Email(
     *     message = "Cette email '{{ value }}'est incorrecte."
     * )
     * @Assert\NotBlank
     */
    private $emailSociete;

    /**
     * @ORM\Column(type="string", length=55, nullable=true)
     * @Assert\Email(
     *     message = "Cette email '{{ value }}'est incorrecte."
     * )
     */
    private $emailPerso;

    /**
     * @ORM\Column(type="string", length=16)
     *@Assert\Length(min = 8, max = 20, minMessage = "trop court", maxMessage = "trop long")
     *@Assert\Regex(pattern="/^(0|(\+[0-9]{2}[. -]?))[1-9]([. -]?[0-9][0-9]){4}$/", message="uniquement des chiffres")
     * @Assert\Type("string")
     */
    private $telephone_societe;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     *@Assert\Length(min = 8, max = 20, minMessage = "min_lenght", maxMessage = "max_lenght")
     *@Assert\Regex(pattern="/^(0|(\+[0-9]{2}[. -]?))[1-9]([. -]?[0-9][0-9]){4}$/ ", message="uniquement des chiffres")
     * @Assert\Type("string")
     */
    private $telephonePerso;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $etatUtilisateur="ACTIF";

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Commande", mappedBy="utilisateur", cascade={"persist","remove"})
     */
    private $listingCommandes;

    /**
     * Utilisateur constructor.
     * @param $listingCommandes
     */
    public function __construct()
    {
        $this->listingCommandes = new ArrayCollection();
    }
    //****************************************
    /**
     * @return Collection|Commande[]
     */
    public function getListingCommandes(): Collection
    {
        return $this->listingCommandes;
    }

    //ajouter une cde
    public function addCommande(Commande $listingCommandes): self
    {
        if (!$this->listingCommandes->contains($listingCommandes)) {
            $this->listingCommandes[] = $listingCommandes;

        }
        return $this;
    }

    //enlever un produit
    public function removeCommande(Commande $listingCommandes): self
    {
        if ($this->listingCommandes->contains($listingCommandes)) {
            $this->listingCommandes->removeElement($listingCommandes);

        }
        return $this;
    }

    //****************************************

    /**
     * @return mixed
     */
    public function getEtatUtilisateur()
    {
        return $this->etatUtilisateur;
    }

    /**
     * @param mixed $etatUtilisateur
     */
    public function setEtatUtilisateur($etatUtilisateur): void
    {
        $this->etatUtilisateur = $etatUtilisateur;
    }



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
        $this->nomDeLaSociete = $nomDeLaSociete." ";

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->nomDeLaSociete;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmailSociete(): ?string
    {
        return $this->emailSociete;
    }

    public function setEmailSociete(string $emailSociete): self
    {
        $this->emailSociete = $emailSociete;

        return $this;
    }

    public function getEmailPerso(): ?string
    {
        return $this->emailPerso;
    }

    public function setEmailPerso(?string $emailPerso): self
    {
        $this->emailPerso = $emailPerso;

        return $this;
    }

    public function getTelephoneSociete(): ?string
    {
        return $this->telephone_societe;
    }

    public function setTelephoneSociete(string $telephone_societe): self
    {
        $this->telephone_societe = $telephone_societe;

        return $this;
    }

    public function getTelephonePerso(): ?string
    {
        return $this->telephonePerso;
    }

    public function setTelephonePerso(?string $telephonePerso): self
    {
        $this->telephonePerso = $telephonePerso;

        return $this;
    }
}
