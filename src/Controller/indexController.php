<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class indexController
{
    /**
     * @Route("/index")
    */
    public function index()
    {
        self::getClientEscaner();
    }


    public static function getClientEscaner()
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
}