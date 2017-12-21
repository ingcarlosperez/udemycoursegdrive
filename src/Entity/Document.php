<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DocumentRepository")
 */
class Document
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fileid;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Person", inversedBy="documents")
     * @ORM\JoinColumn(nullable=true)
     */
    private $person;

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

 
    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of description
     */ 
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @return  self
     */ 
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of fileid
     */ 
    public function getFileid()
    {
        return $this->fileid;
    }

    /**
     * Set the value of fileid
     *
     * @return  self
     */ 
    public function setFileid($fileid)
    {
        $this->fileid = $fileid;

        return $this;
    }

    public function getPerson(): Person
    {
        return $this->person;
    }

    public function setPerson(Person $person)
    {
        $this->person = $person;
    }
}
