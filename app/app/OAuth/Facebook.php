<?php

namespace App\OAuth;

use GuzzleHttp\Client;

class Facebook
{
    const GRAPH_URL = "https://graph.facebook.com/";
    const FB_URL = "https://www.facebook.com/";

    protected $accessToken = '';

    public function __construct($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public static function getUrl($path)
    {
        $version = env('FACEBOOK_GRAPH_VERSION');
        $path = ltrim($path, '/');
        return self::GRAPH_URL."$version/$path";
    }

    public function getUserInfo()
    {
        $client = new Client();
        $response = $client->get(self::getUrl('me'), [
           'query' => [
               'access_token' => $this->accessToken,
               'fields' => 'email'
           ]
        ]);

        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody(), true);
        } else {
            return null;
        }
    }

    public static function getCallbackUrl($token)
    {
        return self::FB_URL.env('FACEBOOK_GRAPH_VERSION')."/dialog/oauth?"
            ."client_id=".env('FACEBOOK_APP_ID')
            ."&redirect_uri=".env('APP_URL')."/social_auth?provider=facebook"
            ."&response_type=code"
            ."&provider=facebook"
            ."&state=$token";
    }

    public static function getAccessToken($code)
    {
        $client = new Client();
        $response = $client->post(self::GRAPH_URL."oauth/access_token", [
            'query' => [
                'code' => $code,
                'client_id' => env('FACEBOOK_APP_ID'),
                'client_secret' => env('FACEBOOK_APP_SECRET'),
                'grant_type' => 'authorization_code',
                'redirect_uri' => env('APP_URL')."/social_auth?provider=facebook"
            ]
        ]);
        if ($response->getStatusCode() === 200) {
            return json_decode($response->getBody(), true)['access_token'];
        }
        return null;
    }
}
