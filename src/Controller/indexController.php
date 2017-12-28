<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Utils\Gdrive;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\Context\RequestStackContext;


class indexController extends AbstractController
{
    /**
     * @Route("/test")
    */
    public function test()
    {
        $folderTMPId = "0BxCE9YuyKsNoZXp5Z3BmUk1fazQ";
        $client = new Gdrive();
        $clientEscaner=$client->getClientEscaner();
        $service = new \Google_Service_Drive($clientEscaner);
        $files=$client->listFilesInFolder($service, $folderTMPId);
        return new Response(
            '<html><body>Lucky number: '.var_dump($files).'</body></html>'
        );
    }
    /**
     * @Route("/", name="processfiles")
     */
    public function index()
    {
        $number= rand(5, 15);
        return $this->render('Gdrive/index.html.twig');//, ['number' => $number,]
    }    

    /**
     * @Route("/checkfiles", name="checkfiles")
     */
    public function checkFiles()
    {
        $number= rand(5, 15);
        return $this->render('Gdrive/checkfiles.html.twig');//, ['number' => $number,]
    }    
}
