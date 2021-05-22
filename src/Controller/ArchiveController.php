<?php

namespace App\Controller;

use App\Service\ApiClient;
use PhpZip\Exception\ZipException;
use PhpZip\ZipFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ArchiveController extends AbstractController
{

    /**
     * @Route("/albums/{albumId<[0-9]+>}/archive",methods={"GET"})
     */
    public function downloadArchiveOfAlbumPhoto($albumId, ApiClient $apiClient)
    {

        //Get Album by id
        $album = $apiClient->getAlbumById($albumId);

        if (array_key_exists("errorCode", $album))
        {
            //Return error message with status code 
            return $this->json(["message"=>$album["message"]], $album["errorCode"]);
        }
      
        //Get Photos by album Id
        $photos = $apiClient->getPhotosByAlbumId($albumId);

        if (array_key_exists("errorCode", $photos))
        {
            //Return error message with status code 
            return $this->json(["message"=>$photos["message"]], $photos["errorCode"]);
        }

        // Create new Zip Archive.
        $zip = new \ZipArchive();

        // The name of the Zip documents.
        $zipName = sprintf("Album %d - %s.zip", $albumId, $album['title']);

        $zip->open($zipName,  \ZipArchive::CREATE);
 
        $csvData = "Nom du fichier, URL, Taille (o),  Mime type, Hash MD5, Largeur (px), Longueur (px)" . PHP_EOL; //these are the columns of CSV file

        foreach($photos as $photo){

            //Get Photo by URL
            $result = $apiClient->getPhotoByUrl($photo["url"]);

            if (array_key_exists("content", $result))
            {
                // The name of the photo.
                $photoName = sprintf("Photo %d - %s%s", $photo["id"], $photo["title"], $result["photoExtension"]); 

                //these are the rows of CSV file
                $csvData .= sprintf(
                    "%s,%s,%s,%s,%s,%s,%s", 
                    $photoName, 
                    $photo["url"], 
                    $result["contentLength"], 
                    $result["contentType"], 
                    md5($result["content"]),
                    $result["width"], 
                    $result["height"]
                    ).PHP_EOL;

                //Add photo to archive
                $zip->addFromString($photoName,$result["content"]);

            }
            
        }

        //Add CSV to archive
        $zip->addFromString("contents.csv",$csvData);

        //Close the archive
        $zip->close();
        
        $response = new Response(file_get_contents($zipName));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $zipName . '"');
        $response->headers->set('Content-length', filesize($zipName));

        // Delete the archive
        unlink($zipName);

        return $response;
       
    }
    
}