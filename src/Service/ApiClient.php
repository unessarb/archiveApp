<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class ApiClient {

    private $client;

    private $apiUrl;

    public function __construct(HttpClientInterface $client,string $apiUrl)
    {
        $this->client = $client;
        $this->apiUrl= $apiUrl;
    }

    public function getAlbumById(int $albumId): array
    {
        try {

            $response = $this->client->request(
                'GET',
                $this->apiUrl.$albumId
            );

            $statusCode = $response->getStatusCode();

            if($statusCode !== Response::HTTP_OK){
                return ["errorCode"=>$statusCode, "message"=>"Album Not Found"];
            }

            return $response->toArray();

        } catch (TransportExceptionInterface $ex) {
            
            return ["errorCode"=>Response::HTTP_INTERNAL_SERVER_ERROR, "message"=>$ex->getMessage()];
            
        }
    }

    public function getPhotosByAlbumId(int $albumId): array
    {
        try {

            $response = $this->client->request(
                'GET',
                $this->apiUrl.$albumId."/photos"
            );

            $statusCode = $response->getStatusCode();

            if($statusCode !== Response::HTTP_OK){
                return ["errorCode"=>$statusCode, "message"=>"Album Not Found"];
            }
            
            return $response->toArray();

        } catch (TransportExceptionInterface $ex) {
            
            return ["errorCode"=>Response::HTTP_INTERNAL_SERVER_ERROR, "message"=>$ex->getMessage()];
            
        }
    }

    public function getPhotoByUrl(string $url): array
    {
        try {

            $response = $this->client->request(
                'GET',
                $url
            );

            $statusCode = $response->getStatusCode();

            if($statusCode !== Response::HTTP_OK){
                return ["errorCode"=>$statusCode, "message"=>"Photo Not Found"];
            }
            
            $info = getimagesize($url);
            $photoExtension = image_type_to_extension($info[2]);

            list($width, $height) = getimagesize($url); 

            return [ 
                "content" => $response->getContent(),
                "photoExtension"=>$photoExtension,
                "contentType"=>$response->getHeaders()['content-type'][0],
                "contentLength"=>$response->getHeaders()['content-length'][0],
                "width"=>$width,
                "height"=>$height
            ];

        } catch (TransportExceptionInterface $ex) {
            
            return ["errorCode"=>Response::HTTP_INTERNAL_SERVER_ERROR, "message"=>$ex->getMessage()];
            
        }
    }
}