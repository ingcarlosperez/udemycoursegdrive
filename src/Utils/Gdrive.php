<?php 

namespace App\Utils;

define('APPLICATION_NAME_ESCANER', 'Generador de acceso para cliente escaner');
define('CREDENTIALS_PATH_ESCANER', dirname(__FILE__).'/../../config/gdrivecredentials/credencialesescaner/credentials.json');
define('CLIENT_SECRET_PATH_ESCANER', dirname(__FILE__).'/../../config/gdrivecredentials/client_secret_escaner.json');

define('SCOPES_ESCANER', implode(' ', array(
        \Google_Service_Drive::DRIVE)
      ));


class Gdrive
{
    /*
    * Create a new Google Drive Client.
    */
    public function getClient()
    {
        $client = new \Google_Client();
        $client->setApplicationName(APPLICATION_NAME_ESCANER);
        $client->setScopes(SCOPES_ESCANER);
        $client->setAuthConfig(CLIENT_SECRET_PATH_ESCANER);
        $client->setAccessType('offline');
    
        $credentialsPath = self::expandHomeDirectory(CREDENTIALS_PATH_ESCANER);
    
        if (file_exists($credentialsPath)) {
            $accessToken = json_decode(file_get_contents($credentialsPath), true);
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));
    
            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
    
            // Store the credentials to disk.
            if (!file_exists(dirname($credentialsPath))) {
                mkdir(dirname($credentialsPath), 0700, true);
            }
            file_put_contents($credentialsPath, json_encode($accessToken));
            printf("Credentials saved to %s\n", $credentialsPath);
        }
    
        $client->setAccessToken($accessToken);
        
            // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
        }
            return $client;
    }
    /*
    * Open Google Drive home directory
    */
    public static function expandHomeDirectory($path)
    {
        $homeDirectory = getenv('HOME');
        if (empty($homeDirectory)) {
            $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
        }
        return str_replace('~', realpath($homeDirectory), $path);
    }

    /*
    * List elements contained in specific folder
    */
    public function listFilesInFolder($service, $folderId)
    {
        $pageToken = null;
        //   do {
            $response = $service->files->listFiles(
                array(
                    'q' => "'".$folderId."' in parents",
                    'spaces' => 'drive',
                    'pageToken' => $pageToken,
                    'fields' => 'nextPageToken, files(id, name)'
                    )
                );
            //   foreach ($response->files as $file) {
            //           printf("Found file: %s (%s)\n", $file->name, $file->id);
            //   }
        //   } while ($pageToken != null);
        return $response->files;
    }

    /*
    * Download a specific file 
    */
    //Descargar archivo
    function downloadFile($service, $fileId)
    {
        $fileMetada = $service->files->get($fileId);
        $file = $service->files->get($fileId, array('alt' => 'media' ));
        $content = $file->getBody()->getContents();
        $data = base64_decode($content);
        
        header("Cache-Control: no-cache private");
        header("Content-Description: File Transfer");
        header("Content-disposition: attachment; filename='".$fileMetada["name"]."'");        
        header("Content-Type: application/octet-stream");
        header("Content-Transfer-Encoding: binary");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $content;
    }

    function searchFiles($service, $folder ="", $nombre_archivo = "")
    {
        $pageToken = null;
        do {
            $response = $service->files->listFiles(
                array(
                   // 'q'=> "mimeType='image/gif' and name contains 'default'",
                    'q' => "name='$nombre_archivo'",
                    'spaces' => 'drive',
                    'pageToken' => $pageToken,
                    'fields' => 'nextPageToken, files(id, name)'
                    )
                );
            foreach ($response->files as $file) {
                    printf("Found file: %s (%s)\n", $file->name, $file->id);
            }
        } while ($pageToken != null);
    }

    //Save files locally
    public static function saveFile($client,$service, $fileId)
    {
        $optParams = array("spaces"=>"drive");
        $fileMetada = $service->files->get($fileId);
        $file = $service->files->get($fileId, array(
        'alt' => 'media' ));
        $content = $file->getBody()->getContents();
        $fileroute=date("Y")."/".date("m")."/".$fileMetada["name"];
        if (!is_dir($_SERVER['DOCUMENT_ROOT'].'/uploads/files/'.date("Y")."/".date("m")."/")) {
            mkdir($_SERVER['DOCUMENT_ROOT'].'/uploads/files/'.date("Y")."/".date("m")."/", 0777, true);
        }
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/uploads/files/'.$fileroute, $content);
        $datafile=array("filename"=>$fileMetada["name"],"routefile" => $fileroute);
        return $datafile;
    }

    public static function moveFile($client,$service, $folderBackupId, $fileId){
        $emptyFileMetadata = new \Google_Service_Drive_DriveFile();
        // Retrieve the existing parents to remove
        $file = $service->files->get($fileId, array('fields' => 'parents'));
       // $previousParents = join(',', $file->parents);
        // Move the file to the new folder
        $file = $service->files->update($fileId, $emptyFileMetadata, array(
            'addParents' => $folderBackupId,
            'removeParents' => $file->parents,
            'fields' => 'id, parents'));
    }
}