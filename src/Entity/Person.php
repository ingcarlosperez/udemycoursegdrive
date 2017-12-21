<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonRepository")
 */
class Person
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\Column(type="string", length=45)
     */
    private $nroid;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $middlename;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $lastname;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Document", mappedBy="person")
     */
    private $documents;

     /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of nroid
     */ 
    public function getNroid()
    {
        return $this->nroid;
    }

    /**
     * Set the value of nroid
     *
     * @return  self
     */ 
    public function setNroid($nroid)
    {
        $this->nroid = $nroid;

        return $this;
    }

    /**
     * Get the value of firstname
     */ 
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set the value of firstname
     *
     * @return  self
     */ 
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get the value of lastname
     */ 
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set the value of lastname
     *
     * @return  self
     */ 
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get the value of middlename
     */ 
    public function getMiddlename()
    {
        return $this->middlename;
    }

    /**
     * Set the value of middlename
     *
     * @return  self
     */ 
    public function setMiddlename($middlename)
    {
        $this->middlename = $middlename;

        return $this;
    }

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    /**
     * @return Collection|Document[]
     */
    public function getDocuments()
    {
        return $this->documents;
    }
}
