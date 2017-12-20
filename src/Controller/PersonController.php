<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Person;

class PersonController extends Controller
{
    /**
     * @Route("/person", name="person")
     */
    public function index()
    {
        // replace this line with your own code!
       // you can fetch the EntityManager via $this->getDoctrine()
        // or you can add an argument to your action: index(EntityManagerInterface $em)
        $em = $this->getDoctrine()->getManager();

        $person = new Person();
        $person->setNroId("10011184");
        $person->setFirstname("Carlos");
        $person->setMiddlename("Alfonso");
        $person->setLastname("Pérez Rivera");

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $em->persist($person);

        // actually executes the queries (i.e. the INSERT query)
        $em->flush();

        return new Response('Person saved with ID: '.$person->getId());
    }
}
