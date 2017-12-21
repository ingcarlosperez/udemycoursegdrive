<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Utils\Gdrive;

class indexController
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
}
