<?php

namespace App\OAuth;

use GuzzleHttp\Client;

class Google
{
    const AUTH_URL = "https://accounts.google.com/o/oauth2/auth";
    const ACCESS_TOKEN_URL = "https://www.googleapis.com/oauth2/v4/token";
    const API_URL = "https://www.googleapis.com/plus/v1/people";

    protected $accessToken = '';

    public function __construct($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getUserInfo()
    {
        $client = new Client();
        $response = $client->get(self::API_URL."/me", [
            'query' => [
                'access_token' => $this->accessToken,
                'scope' => 'email'
            ]
        ]);

        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getBody(), true);
            $data['first_name'] = $data['name']['givenName'];
            $data['last_name'] = $data['name']['familyName'];
            $data['email'] = $data['emails'][0]['value'];
            return $data;
        } else {
            return null;
        }
    }

    public static function getCallbackUrl($token)
    {
        return self::AUTH_URL
            ."?client_id=".env('GOOGLE_APP_ID')
            ."&redirect_uri=".env('APP_URL').":8000"."/social_auth?provider=google"
            ."&response_type=code"
            ."&state=$token"
            ."&scope=email";
    }

    public static function getAccessToken($code)
    {
        $client = new Client();
        $response = $client->post(self::ACCESS_TOKEN_URL, [
           'query' => [
               'code' => $code,
               'client_id' => env('GOOGLE_APP_ID'),
               'client_secret' => env('GOOGLE_APP_SECRET'),
               'redirect_uri' => env('APP_URL').":8000/social_auth?provider=google",
               'grant_type' => 'authorization_code'
           ]
        ]);
        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody(), true)['access_token'];
        }
    }
}
