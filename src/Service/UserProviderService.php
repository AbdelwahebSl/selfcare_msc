<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Twig\Token;

class UserProviderService
{
    public function __construct()
    {
    }

    public function getJobTitle(string $token)
    {

        $client = HttpClient::create();
        $select='$select';
        $response = $client->request('GET',"https://graph.microsoft.com/v1.0/me?$select=employeeId,department,jobTitle,phoneNumber", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ]
        ]);
        try {

            $content = $response->getContent(false);

            $content = json_decode($content, true);
            return $content;

        } catch (\Exception $exception) {
            $content = $exception->getResponse()->getContent(false);
            $content = json_decode($content, true);
            return  [
                'code' => $content['error']['code'],
                'message' => $content['error']['message']
            ];
        }


    }

}