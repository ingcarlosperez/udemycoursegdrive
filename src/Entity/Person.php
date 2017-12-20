<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

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
}
