<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StartUpRepository")
 */
class StartUp
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $siret;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tel;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StartUpRelation", mappedBy="startUp", fetch="EAGER")
     */
    private $startUpRelations;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Evenements", mappedBy="startups", fetch="EAGER")
     */
    private $evenements;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Person", mappedBy="startup", cascade={"persist", "remove"})
     */
    private $person;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ChampsSoumis", mappedBy="startup", fetch="EAGER")
     */
    private $champsSoumis;

    public function __construct()
    {
        $this->partners = new ArrayCollection();
        $this->externalCompanies = new ArrayCollection();

        $this->champsSoumis = new ArrayCollection();
        $this->evenements = new ArrayCollection();
        $this->startUpRelations = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getSiret(): ?int
    {
        return $this->siret;
    }

    public function setSiret(?int $siret): self
    {
        $this->siret = $siret;

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): self
    {
        $this->tel = $tel;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection|StartUpRelation[]
     */
    public function getStartUpRelations(): Collection
    {
        return $this->startUpRelations;
    }

    public function addStartUpRelation(StartUpRelation $startUpRelation): self
    {
        if (!$this->startUpRelations->contains($startUpRelation)) {
            $this->startUpRelations[] = $startUpRelation;
            $startUpRelation->setStartUp($this);
        }
        return $this;
    }
    /**
     * @return Collection|Evenements[]
     */
    public function getEvenements(): Collection
    {
        return $this->evenements;
    }

    public function addEvenement(Evenements $evenement): self
    {
        if (!$this->evenements->contains($evenement)) {
            $this->evenements[] = $evenement;
            $evenement->addStartup($this);
        }

        return $this;
    }

    public function removeStartUpRelation(StartUpRelation $startUpRelation): self
    {
        if ($this->startUpRelations->contains($startUpRelation)) {
            $this->startUpRelations->removeElement($startUpRelation);
            // set the owning side to null (unless already changed)
            if ($startUpRelation->getStartUp() === $this) {
                $startUpRelation->setStartUp(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection|ChampsSoumis[]
     */
    public function getChampsSoumis(): Collection
    {
        return $this->champsSoumis;
    }

    public function addChampsSoumi(ChampsSoumis $champsSoumi): self
    {
        if (!$this->champsSoumis->contains($champsSoumi)) {
            $this->champsSoumis[] = $champsSoumi;
            $champsSoumi->setStartup($this);
        }

        return $this;
    }

    public function removeChampsSoumi(ChampsSoumis $champsSoumi): self
    {
        if ($this->champsSoumis->contains($champsSoumi)) {
            $this->champsSoumis->removeElement($champsSoumi);
            // set the owning side to null (unless already changed)
            if ($champsSoumi->getStartup() === $this) {
                $champsSoumi->setStartup(null);
            }
        }

        return $this;
    }


  
    public function removeEvenement(Evenements $evenement): self
    {
        if ($this->evenements->contains($evenement)) {
            $this->evenements->removeElement($evenement);
            $evenement->removeStartup($this);
        }
        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

        // set (or unset) the owning side of the relation if necessary
        $newStartup = $person === null ? null : $this;
        if ($newStartup !== $person->getStartup()) {
            $person->setStartup($newStartup);
        }

        return $this;
    }

}
