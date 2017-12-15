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
    public function getClientEscaner()
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
  
    public static function expandHomeDirectory($path)
    {
        $homeDirectory = getenv('HOME');
        if (empty($homeDirectory)) {
            $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
        }
        return str_replace('~', realpath($homeDirectory), $path);
    }

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
}