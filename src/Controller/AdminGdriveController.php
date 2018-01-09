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
        
        foreach($files as $file){
            if(isset($person))unset($person);
            $responsefile = "";
            $datafile = array();
            $fileinfo=explode(".",$file->name);
            $datafile=explode("_",$fileinfo[0]);
            if(isset($datafile[1])){
                $person = $this->getDoctrine()
                    ->getRepository(Person::class)
                    ->findOneByNroid($datafile[1]);
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
                $personData=array("nroid"=>$person->getNroid(), "firstname"=>$person->getFirstname(),"middlename"=>$person->getMiddlename(),"lastname"=>$person->getLastname());
                $resultfilesDataTable['data'][]=array("id"=>$file->id,"filename"=>$file->name,"person"=>$personData,"date"=>$datafile[0], "type"=>$fileinfo[1],"validfile"=>$validfile, "responsefile"=>$responsefile);
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
        $request = Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();
        $folderBackupId = '1sFb52uEv6PFG7nauOXF_lMrNsfrPSFRx';
        $client = new Gdrive();
        $scannerClient=$client->getClient();
        $service = new \Google_Service_Drive($scannerClient);
        // $filestoprocess=$request->getParameter('filestoprocess');
        $filestoprocess=explode(",",$request->query->get('filestoprocess'));
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
        return new JsonResponse(array("response"=> "Ok"));
    }   

  public static function validateDate($date, $format = 'Y-m-d')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

        /**
     * @Route("/listprocessedfiles", name="list_processed_files")
     */
    public function listProcessedFiles()
    {
        $request = Request::createFromGlobals();
        $resultProcessedFiles['draw'] = $request->query->get('draw');//$draw
        $resultProcessedFiles['recordsTotal'] = '';
        $resultProcessedFiles['recordsFiltered'] = '';
        $resultProcessedFiles['data'] = array();    
        //consulta sql
        $documents = $this->getDoctrine()
                    ->getRepository(Document::class)
                    ->findAll();

        //recorrer resultados
        foreach($documents as $document){
            $personName=$document->getPerson()->getFirstname()." ".$document->getPerson()->getMiddlename()." ".$document->getPerson()->getlastname();
            $resultProcessedFiles['data'][]=array("person_name"=>$personName,"description"=>$document->getDescription(),"filename"=>$document->getName(),"fileid"=>$document->getFileid());
        }
        $resultProcessedFiles['recordsTotal']=count($resultProcessedFiles['data']);
        $resultProcessedFiles['recordsFiltered']=count($resultProcessedFiles['data']);

        if ($resultProcessedFiles != false) {
            $response = new JsonResponse($resultProcessedFiles);            
        }else{
            $response = new JsonResponse([]);
        }
        return $response;
    }


    /**
     * @Route("/downloadfilefromgdrive", name="download_file_from_gdrive")
    */
    public function downloadFileFromGdrive()
    {
        $request = Request::createFromGlobals();
        $client = new Gdrive();
        $scannerClient=$client->getClient();
        $service = new \Google_Service_Drive($scannerClient);
        $client->downloadFile($service, $request->query->get('fileid'));
    }
}
