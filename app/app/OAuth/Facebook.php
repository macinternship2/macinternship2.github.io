<?php

namespace App\OAuth;

use GuzzleHttp\Client;

class Facebook
{
    const GRAPH_API_VERSION = "";
    const GRAPH_URL = "https://graph.facebook.com/";
    const FB_URL = "https://www.facebook.com/";

    protected $accessToken = '';

    public function __construct($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public static function getUrl($path)
    {
        $version = self::GRAPH_API_VERSION;
        $path = ltrim($path, '/');
        return self::GRAPH_URL."$version/$path";
    }

    public function getUserInfo()
    {
        $client = new Client();
        $response = $client->get(self::getUrl('/me'), [
           'query' => [
               'access_token' => $this->accessToken,
               'fields' => 'email,public_profile'
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
        return self::FB_URL.self::GRAPH_API_VERSION."/dialog/oauth?"
            ."client_id=".env('FACEBOOK_APP_ID')
            ."&redirect_uri=".env('APP_URL')."/social_auth"
            ."&response_type=token"
            ."&provider=facebook"
            ."&state=$token";
    }
}
