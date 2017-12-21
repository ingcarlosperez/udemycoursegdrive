<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Utils\Gdrive;
use App\Entity\Person;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminGdriveController extends Controller
{
    /**
     * @Route("/getfilestoprocess", name="get_files_process")
     */
    public function getFilesToProcess()
    {
        $optParams = array("spaces"=>"drive");
        $folderScannedFilesId = "12wUPUA2TBNS-vg4Bm7UpesYkW874RV8_";
        $folderBackupId = '1sFb52uEv6PFG7nauOXF_lMrNsfrPSFRx';
        
        $client = new Gdrive();
        $scannerClient=$client->getClient();

        
        $service = new \Google_Service_Drive($scannerClient);
        $files=$client->listFilesInFolder($service, $folderScannedFilesId);

        $this->filestoprocess=array();    
        $filestoprocess = array();
        $validfile = true;

       // $draw = $request->getParameter('draw');
        $resultfilesDataTable['draw'] = 1;//$draw
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
                $resultfilesDataTable['data'][]=array("id"=>$file->id,"filename"=>$file->name,"person"=>null,"date"=>null, "type"=>"","validfile"=>$validfile, "responsefile"=>$responsefile);                
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
