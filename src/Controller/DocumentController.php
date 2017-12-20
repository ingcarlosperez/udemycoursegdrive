<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Document;
use App\Entity\Person;

class DocumentController extends Controller
{
    /**
     * @Route("/document", name="document")
     */
    public function index()
    {
        // replace this line with your own code!
       // you can fetch the EntityManager via $this->getDoctrine()
        // or you can add an argument to your action: index(EntityManagerInterface $em)
        $em = $this->getDoctrine()->getManager();
        $person = $this->getDoctrine()
                ->getRepository(Person::class)
                ->find(3);
        $document = new Document();
        $person = new Document();
        $document->setDescription("Test description");
        $document->setName("Test name");
        $document->setPerson($person);

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $em->persist($document);

        // actually executes the queries (i.e. the INSERT query)
        $em->flush();

        return new Response('document saved with ID: '.$document->getId());
    }
}
