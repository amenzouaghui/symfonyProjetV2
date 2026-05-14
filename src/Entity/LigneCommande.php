<?php
namespace App\Entity;

use App\Repository\LigneCommandeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LigneCommandeRepository::class)]
#[ORM\Table(name: 'ligne_commande')]
class LigneCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\Column]
    private ?float $prixUnitaire = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomMeuble = null;

    #[ORM\ManyToOne(targetEntity: Commande::class)]
    #[ORM\JoinColumn(name: 'commande_id', nullable: false)]
    private ?Commande $commande = null;

    #[ORM\ManyToOne(targetEntity: Meuble::class)]
    #[ORM\JoinColumn(name: 'meuble_id', nullable: true)]
    private ?Meuble $meuble = null;

    public function getId(): ?int { return $this->id; }

    public function getQuantite(): ?int { return $this->quantite; }
    public function setQuantite(int $quantite): static { $this->quantite = $quantite; return $this; }

    public function getPrixUnitaire(): ?float { return $this->prixUnitaire; }
    public function setPrixUnitaire(float $prixUnitaire): static { $this->prixUnitaire = $prixUnitaire; return $this; }

    public function getNomMeuble(): ?string { return $this->nomMeuble; }
    public function setNomMeuble(?string $nomMeuble): static { $this->nomMeuble = $nomMeuble; return $this; }

    public function getCommande(): ?Commande { return $this->commande; }
    public function setCommande(?Commande $commande): static { $this->commande = $commande; return $this; }

    public function getMeuble(): ?Meuble { return $this->meuble; }
    public function setMeuble(?Meuble $meuble): static { $this->meuble = $meuble; return $this; }
}