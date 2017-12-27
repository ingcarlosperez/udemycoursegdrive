<?php

namespace App\Controller;

use App\Utils\Gdrive;
use App\Entity\Person;
use App\Entity\Document;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class AdminGdriveController extends Controller
{
    /**
     * @Route("/getfilestoprocess", name="get_files_process")
     */
    public function getFilesToProcess()
    {
        $request = Request::createFromGlobals();
        $optParams = array("spaces"=>"drive");
        $folderScannedFilesId = "12wUPUA2TBNS-vg4Bm7UpesYkW874RV8_";        
        
        $client = new Gdrive();
        $scannerClient=$client->getClient();

        
        $service = new \Google_Service_Drive($scannerClient);
        $files=$client->listFilesInFolder($service, $folderScannedFilesId);

        $this->filestoprocess=array();    
        $filestoprocess = array();
        $validfile = true;

       // $draw = $request->getParameter('draw');
        $resultfilesDataTable['draw'] = $request->query->get('draw');//$draw
        $resultfilesDataTable['recordsTotal'] = '';
        $resultfilesDataTable['recordsFiltered'] = '';
        $resultfilesDataTable['data'] = array();    
        $responsefile = "";
        $datafile = array();

        foreach($files as $file){
            $fileinfo=explode(".",$file->name);
            $datafile=explode("_",$fileinfo[0]);
            if(isset($datafile[1])){
                $person = $this->getDoctrine()
                    ->getRepository(Person::class)
                    ->findByNroid($datafile[1]);
            }

            if(isset($person)&&self::validateDate($datafile[0])&&isset($datafile[0])){
                $filestoprocess[]=$file->id;
                $responsefile = "Ok";
            }else{
                $validfile = false;
                if(!isset($person)) {
                    $responsefile .="Person doesn't exist";
                }
                if(!self::validateDate($datafile[0])||!isset($datafile[0])){
                     $responsefile .=" Invalid date";
                }
            }
            if(isset($person)&&isset($datafile[0])&&isset($fileinfo[1]))
            {
                $resultfilesDataTable['data'][]=array("id"=>$file->id,"filename"=>$file->name,"person"=>self::objectToArray($person),"date"=>$datafile[0], "type"=>$fileinfo[1],"validfile"=>$validfile, "responsefile"=>$responsefile);
            }else{
                $resultfilesDataTable['data'][]=array("id"=>$file->id,"filename"=>$file->name,"person"=>null,"date"=>null, "type"=>null,"validfile"=>$validfile, "responsefile"=>$responsefile);                
            }
            $validfile = true;
            $responsefile="";
        }

        $resultfilesDataTable['filestoprocess'] = $filestoprocess;
        $resultfilesDataTable['recordsTotal']=count($resultfilesDataTable['data']);
        $resultfilesDataTable['recordsFiltered']=count($resultfilesDataTable['data']);

        if ($resultfilesDataTable != false) {
            $response = new JsonResponse($resultfilesDataTable);
            
        }else{
            $response = new JsonResponse([]);
        }
        return $response;
    } 

    /**
     * @Route("/processfilesfromgdrive", name="process_files_from_gdrive")
    */
    public function processFilesFromGdrive()
    {
        $em = $this->getDoctrine()->getManager();
        $folderBackupId = '1sFb52uEv6PFG7nauOXF_lMrNsfrPSFRx';
        $client = new Gdrive();
        $scannerClient=$client->getClient();
        $service = new \Google_Service_Drive($scannerClient);
        // $filestoprocess=$request->getParameter('filestoprocess');
        $filestoprocess=array("1C7r6tG5gm18j47kIilzK3rKfj2r_kpM-");
       // $filestoprocessdecode=json_decode($filestoprocess);
        foreach($filestoprocess as $filetoprocess){
            //Copy file to local server
            $datafile=$client->saveFile($client,$service, $filetoprocess);
            //Move file to def folder in Google Drive Account
            $client->moveFile($client,$service, $folderBackupId, $filetoprocess);
            //Crear registro de archivo en Entidad 
            $datafilename=explode("_",$datafile["filename"]);
            $person = $this->getDoctrine()
                ->getRepository(Person::class)
                ->findByNroid($datafilename[1]);
            $document=new Document();
            $document->setPerson($person[0]);
            $document->setDescription($datafilename[2]);
            $document->setName($datafile["routefile"]);
            $document->setFileid($filetoprocess);
            $em->persist($document);
            $em->flush();        
        }
        return new JsonResponse(array("message"=> "Ok"));
    }   

    public static function objectToArray($data)
    {
        if(is_array($data) || is_object($data))
        {
            $result = array();
    
            foreach($data as $key => $value) {
                $result[$key] = self::objectToArray($value);
            }
    
            return $result;
        }
    
        return $data;
    }

  public static function validateDate($date, $format = 'Y-m-d')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}
